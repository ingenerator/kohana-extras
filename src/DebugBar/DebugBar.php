<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DebugBar;


use DebugBar\Bridge\DoctrineCollector;
use DebugBar\StandardDebugBar;
use Doctrine\ORM\EntityManagerInterface;
use Ingenerator\KohanaExtras\DebugBar\DebugLogWriter;
use Ingenerator\KohanaExtras\DebugBar\DebugStackTrace;

/**
 * Simple wrapper for DebugBar to manage availability on the site
 *
 * @package Ingenerator\Debug
 */
class DebugBar
{
    /**
     * @var \DebugBar\DebugBar
     */
    protected $bar;

    /**
     * @var bool
     */
    protected $is_active = FALSE;

    /**
     * @var \Dependency_Container
     */
    protected $dependencies;

    /**
     * Explicitly and intentionally takes no direct constructor dependencies to allow
     * safe access to the debug bar from anywhere in the system even in production with
     * no impact unless the bar has been initialised.
     *
     * @param \Dependencies $dependencies
     */
    public function __construct(\Dependencies $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    public function initialise()
    {
        if (\Kohana::$environment !== \Kohana::DEVELOPMENT) {
            throw new \BadMethodCallException(
                'Cannot use debug bar outside development environment'
            );
        }
        $this->bar = new StandardDebugBar;
        $this->initDoctrineBar($this->dependencies->get('doctrine.entity_manager'));
        $this->initLogCollector($this->dependencies->get('kohana.log'));
        $this->is_active = TRUE;
    }

    protected function initDoctrineBar(EntityManagerInterface $em)
    {
        $stack = new DebugDoctrineQueryTrace;
        $em->getConnection()->getConfiguration()->setSQLLogger($stack);
        $this->bar->addCollector(new DoctrineCollector($stack));
        $this->bar->getJavascriptRenderer()->addAssets(
            [__DIR__.'/../../assets/debug_bar/debug-query-trace.css'],
            []
        );
    }

    protected function initLogCollector(\Kohana_Log $log)
    {
        $log->attach(new DebugLogWriter($this->bar));
    }

    /**
     * @return string
     */
    public function render()
    {
        $renderer = $this->bar->getJavascriptRenderer();
        $renderer->setIncludeVendors('css');
        $asset_hash = \md5(\json_encode($renderer->getAssets()));
        $base_url   = '/assets/compiled/debug-bar';
        $base_path  = DOCROOT.$base_url;
        if ( ! \is_dir($base_path)) {
            \mkdir($base_path, 0777, TRUE);
        }
        if ( ! \file_exists("$base_path/debug-$asset_hash.css")) {
            $renderer->dumpCssAssets("$base_path/debug-$asset_hash.css");
        }
        if ( ! \file_exists("$base_path/debug-$asset_hash.js")) {
            $renderer->dumpJsAssets("$base_path/debug-$asset_hash.js");
        }

        return '<link href="'."$base_url/debug-$asset_hash.css".'" rel="stylesheet">'
            .'<script src="'."$base_url/debug-$asset_hash.js".'"></script>'
            .$renderer->render();
    }

    /**
     * Park debug bar data on a redirect
     */
    public function onRedirect()
    {
        if ($this->isActive()) {
            $this->bar->stackData();
        }
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->is_active;
    }
}

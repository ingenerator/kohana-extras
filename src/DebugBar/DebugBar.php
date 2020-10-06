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
use function basename;
use function copy;
use function glob;
use function is_dir;
use function json_encode;
use function md5;
use function mkdir;

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

        $debugbar_root = '/dev-assets/debugbar/';
        $hash          = md5(json_encode($renderer->getAssets()));
        $asset_url     = $debugbar_root.$hash;
        $asset_path    = DOCROOT.$asset_url;
        if ( ! is_dir($asset_path)) {
            mkdir($asset_path, 0777, TRUE);
            $renderer->dumpCssAssets("$asset_path/debugbar.css");
            $renderer->dumpJsAssets("$asset_path/debugbar.js");

            $font_path = DOCROOT.$debugbar_root.'fonts';
            if ( ! is_dir($font_path)) {
                mkdir($font_path, 0777, TRUE);
                $src = APPPATH.'../vendor/maximebf/debugbar/src/DebugBar/Resources/vendor/font-awesome/fonts/*.*';
                foreach (glob($src) as $file) {
                    copy($file, $font_path.'/'.basename($file));
                }
            }
        }

        return '<link href="'."$asset_url/debugbar.css".'" rel="stylesheet">'
            .'<script src="'."$asset_url/debugbar.js".'"></script>'
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

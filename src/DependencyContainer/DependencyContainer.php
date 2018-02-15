<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyContainer;


class DependencyContainer extends \Dependency_Container
{
    /**
     * @var DependencyContainer
     */
    protected static $instance;

    /**
     * @param \Dependency_Definition_List $config_file
     */
    public function __construct($config_file)
    {
        if ( ! is_readable($config_file)) {
            throw new \InvalidArgumentException('No config file in '.$config_file);
        }

        $config = (new DependencyConfigParser)->parse(require($config_file));

        parent::__construct(\Dependency_Definition_List::factory()->from_array($config));
        $this->_cache('dependencies', $this);
    }

    /**
     * @return DependencyContainer
     */
    public static function instance()
    {
        if ( ! static::$instance) {
            throw new \LogicException(static::class.' has not been initialised');
        }

        return static::$instance;
    }

    /**
     * @param string $config_file
     *
     * @return DependencyContainer
     */
    public static function initialise($config_file)
    {
        if (static::$instance) {
            throw new \LogicException(static::class.' has already been initialised');
        }

        static::$instance = new static($config_file);

        return static::$instance;
    }

    /**
     * @return array
     */
    public function listServices()
    {
        $services = [];
        foreach ($this->_definitions as $key => $definition) {
            $services[] = $key;
        }

        return $services;
    }

}

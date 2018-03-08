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
     * @param array $config
     */
    public function __construct(array $config)
    {
        $config = (new DependencyConfigParser)->parse($config);

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

        static::$instance = static::fromFile($config_file);

        return static::$instance;
    }

    /**
     * @param string $config_file
     *
     * @return static
     */
    public static function fromFile($config_file)
    {
        if ( ! is_readable($config_file)) {
            throw new \InvalidArgumentException('No config file in '.$config_file);
        }

        return new static(require $config_file);
    }

    /**
     * @param string $service
     *
     * @return bool
     */
    public function has($service)
    {
        return (
            isset($this->_cache[$service])
            OR
            isset($this->_definitions[$service])
        );
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

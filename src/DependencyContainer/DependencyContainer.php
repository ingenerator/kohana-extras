<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyContainer;


use Ingenerator\PHPUtils\Object\InitialisableSingletonTrait;

/**
 * @method static DependencyContainer instance()
 */
class DependencyContainer extends \Dependency_Container
{

    use InitialisableSingletonTrait;

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
     * @param string $config_file
     *
     * @return DependencyContainer
     */
    public static function initialiseFromFile(string $config_file)
    {
        static::initialise(function () use ($config_file) {
            return static::fromFile($config_file);
        });

        return static::instance();
    }

    /**
     * @param string $config_file
     *
     * @return static
     */
    public static function fromFile($config_file)
    {
        if ( ! \is_readable($config_file)) {
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

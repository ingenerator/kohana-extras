<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyContainer;


use Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer;

class DependencyContainerTest extends \PHPUnit_Framework_TestCase
{
    protected $old_instance;

    /**
     * @expectedException \LogicException
     */
    public function test_it_throws_from_instance_if_not_initialised()
    {
        DependencyContainer::instance();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_it_throws_on_initialise_if_no_config_file()
    {
        new DependencyContainer('/no/file/here.php');
    }

    public function test_it_is_initialisable_container_with_valid_config_path()
    {
        $container = $this->newSubjectWithConfigFile([]);
        $this->assertInstanceOf(DependencyContainer::class, $container);
        $this->assertInstanceOf(\Dependency_Container::class, $container);
    }

    public function test_it_loads_config_directly_from_dependencies_config_file()
    {
        $container = $this->newSubjectWithConfigFile(
            [
                'date' => [
                    'time' => [
                        '_settings' => ['class' => '\DateTime', 'arguments' => ['2018-01-10  10:00:00']],
                    ],
                ],
            ]
        );
        $date      = $container->get('date.time');
        $this->assertInstanceOf(\DateTime::class, $date);
        $this->assertEquals('2018-01-10 10:00:00', $date->format('Y-m-d H:i:s'));
    }

    public function test_it_incorporates_services_from_array_of_includes()
    {
        $container = $this->newSubjectWithConfigFile(
            [
                '_include' => [
                    [
                        'date' => [
                            'time' => [
                                '_settings' => ['class' => '\DateTime', 'arguments' => ['2018-01-10  10:00:00']],
                            ],
                        ],
                    ],
                ],
                'date'     => [
                    'immutable' => [
                        '_settings' => ['class' => '\DateTimeImmutable', 'arguments' => []],
                    ],
                ],
            ]
        );

        $this->assertInstanceOf(\DateTime::class, $container->get('date.time'));
        $this->assertInstanceOf(\DateTimeImmutable::class, $container->get('date.immutable'));
    }

    public function test_it_provides_itself_as_dependencies_key()
    {
        $container = $this->newSubjectWithConfigFile([]);
        $this->assertSame($container, $container->get('dependencies'));
    }

    public function test_it_can_make_singleton_initialised_from_file_path()
    {
        $container = $this->withConfigFile([], function ($f) { return DependencyContainer::initialise($f); });
        $this->assertSame($container, DependencyContainer::instance(), '::instance() should match ::initialise()');
        $this->assertSame($container, DependencyContainer::instance(), '::instance() should match ::instance()');
    }

    public function test_it_can_make_child_class_singleton()
    {
        $container = $this->withConfigFile(
            [],
            function ($tmp_file) { return CustomDependencyContainer::initialise($tmp_file); }
        );
        $this->assertInstanceOf(CustomDependencyContainer::class, $container);
    }

    public function test_it_throws_on_attempt_to_reinitialise()
    {
        $container = $this->withConfigFile([], function ($f) { return DependencyContainer::initialise($f); });

        try {
            $container = $this->withConfigFile([], function ($f) { return DependencyContainer::initialise($f); });
            $this->fail('Expected \LogicException, none got');
        } catch (\LogicException $e) {
            // Expected
        }
    }

    public function test_it_lists_all_defined_services()
    {
        $container = $this->newSubjectWithConfigFile(
            [
                '_include' => [
                    [
                        'date' => [
                            'time' => [
                                '_settings' => ['class' => '\DateTime', 'arguments' => ['2018-01-10  10:00:00']],
                            ],
                        ],
                    ],
                ],
                'any'      => [
                    'thing' => [
                        'else' => ['_settings' => ['class' => 'stdClass']],
                    ],
                ],
                'date'     => [
                    'immutable' => [
                        '_settings' => ['class' => '\DateTimeImmutable', 'arguments' => []],
                    ],
                ],
            ]
        );

        $this->assertEquals(['date.time', 'date.immutable', 'any.thing.else'], $container->listServices());
    }

    public function setUp()
    {
        parent::setUp();
        $this->old_instance = $this->resetSingleton(NULL);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->resetSingleton($this->old_instance);
    }

    protected function resetSingleton($new_instance)
    {
        $fn = \Closure::bind(
            function ($new) {
                $old                           = DependencyContainer::$instance;
                DependencyContainer::$instance = $new;

                return $old;
            },
            NULL,
            DependencyContainer::class
        );

        return $fn($new_instance);
    }


    /**
     * @param $conf
     *
     * @return DependencyContainer
     */
    protected function newSubjectWithConfigFile(array $conf)
    {
        return $this->withConfigFile($conf, function ($tmp_file) { return new DependencyContainer($tmp_file); });
    }

    protected function withConfigFile(array $conf, $callable)
    {
        $tmp_file = tempnam(sys_get_temp_dir(), '/dependencies.php');
        file_put_contents($tmp_file, '<?php return '.var_export($conf, TRUE).';');
        try {
            return $callable($tmp_file);
        } finally {
            unlink($tmp_file);
        }
    }

}

class CustomDependencyContainer extends DependencyContainer
{
}


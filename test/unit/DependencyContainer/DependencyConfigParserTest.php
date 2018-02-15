<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyContainer;


use Ingenerator\KohanaExtras\DependencyContainer\DependencyConfigParser;

class DependencyConfigParserTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(DependencyConfigParser::class, $this->newSubject());
    }

    public function test_it_parses_simple_array_without_change()
    {
        $config = [
            'date' => [
                'time' => [
                    '_settings' => ['class' => '\DateTime', 'arguments' => ['2018-01-10  10:00:00']],
                ],
            ],
        ];

        $this->assertSame(
            $config,
            $this->newSubject()->parse($config)
        );
    }

    public function test_it_merges_includes_into_main_array()
    {
        $config = [
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
        ];

        $this->assertSame(
            [
                'date' => [
                    'time'      => [
                        '_settings' => ['class' => '\DateTime', 'arguments' => ['2018-01-10  10:00:00']],
                    ],
                    'immutable' => [
                        '_settings' => ['class' => '\DateTimeImmutable', 'arguments' => []],
                    ],
                ],
            ],
            $this->newSubject()->parse($config)
        );
    }

    public function test_it_merges_multiple_includes_to_same_groups()
    {
        $config = [
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
        ];

        $this->assertEquals(
            [
                'date' => [
                    'time'      => [
                        '_settings' => ['class' => '\DateTime', 'arguments' => ['2018-01-10  10:00:00']],
                    ],
                    'immutable' => [
                        '_settings' => ['class' => '\DateTimeImmutable', 'arguments' => []],
                    ],
                ],
            ],
            $this->newSubject()->parse($config)
        );
    }

    public function test_it_overrides_included_config_with_custom_config()
    {
        $config = [
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
                'time' => [
                    '_settings' => ['class' => '\DateTimeImmutable'],
                ],
            ],
        ];

        $this->assertEquals(
            [
                'date' => [
                    'time' => [
                        '_settings' => ['class' => '\DateTimeImmutable', 'arguments' => ['2018-01-10  10:00:00']],
                    ],
                ],
            ],
            $this->newSubject()->parse($config)
        );
    }

    protected function newSubject()
    {
        return new DependencyConfigParser;
    }

}

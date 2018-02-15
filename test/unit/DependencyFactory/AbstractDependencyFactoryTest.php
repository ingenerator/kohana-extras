<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\DependencyFactory\KohanaCoreFactory;
use Ingenerator\KohanaExtras\DependencyFactory\MissingOptionalDependencyException;

abstract class AbstractDependencyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $service     key to locate
     * @param array  $definitions the array of definitions provided by this factory
     *
     * @return mixed
     */
    protected function assertDefinesService($service, array $definitions)
    {
        $list = \Dependency_Definition_List::factory()->from_array($definitions);
        try {
            $list->get($service);
        } catch (\Exception $e) {
            $this->fail('Service `'.$service.'` is not defined: ['.get_class($e).'] '.$e->getMessage());
        }

        $container = new \Dependency_Container($list);

        try {
            return $container->get($service);
        } catch (\Dependency_Exception $e) {
            $this->fail('Cannot instantiate service `'.$service.'` - missing dependency? : '.$e->getMessage());
        } catch (\Exception $e) {
            $this->fail('Cannot instantiate service `'.$service.'`: ['.get_class($e).'] '.$e->getMessage());
        }
    }

    /**
     * Test a service that needs optional dependencies, ignoring the failure if we're not running with optional deps
     * installed.
     *
     * @param callable $callable
     */
    protected function assertOptionalService($callable)
    {
        try {
            $callable();
        } catch (MissingOptionalDependencyException $e) {
            if (getenv('WITH_OPTIONAL_DEPENDENCIES')) {
                throw $e;
            } else {
                // Accept this, we're not expecting it to actually create the dependency so we can pass
            }
        }
    }
}

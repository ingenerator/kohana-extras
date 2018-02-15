<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\DependencyFactory\KohanaCoreFactory;

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
}

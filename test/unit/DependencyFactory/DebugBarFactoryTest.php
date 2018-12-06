<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\DebugBar\DebugBar;
use Ingenerator\KohanaExtras\DependencyFactory\DebugBarFactory;

class DebugBarFactoryTest extends AbstractDependencyFactoryTest
{

    public function test_it_defines_debug_bar()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    DebugBar::class,
                    $this->assertDefinesService('debug_bar.bar', DebugBarFactory::definitions())
                );
            }
        );
    }
}

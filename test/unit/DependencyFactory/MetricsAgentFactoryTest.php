<?php
/**
 * @author    Craig Gosman <craig@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\DependencyFactory\MetricsAgentFactory;
use Ingenerator\PHPUtils\Monitoring\NullMetricsAgent;
use Ingenerator\PHPUtils\Monitoring\OperationTimer;

class MetricsAgentFactoryTest extends AbstractDependencyFactoryTest
{

    public function test_it_defines_metrics_agent()
    {
        $this->assertInstanceOf(
            NullMetricsAgent::class,
            $this->assertDefinesService('metrics.agent', MetricsAgentFactory::definitionsNullAgent())
        );
        $this->assertInstanceOf(
            OperationTimer::class,
            $this->assertDefinesService('metrics.timer', MetricsAgentFactory::definitionsNullAgent())
        );
    }

}

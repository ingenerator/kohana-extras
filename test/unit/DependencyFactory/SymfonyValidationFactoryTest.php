<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\DependencyFactory\DoctrineFactory;
use Ingenerator\KohanaExtras\DependencyFactory\SymfonyValidationFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SymfonyValidationFactoryTest extends AbstractDependencyFactoryTest
{

    public function test_it_defines_validator()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    ValidatorInterface::class,
                    $this->assertDefinesService('validation.validator', SymfonyValidationFactory::definitions())
                );
            }
        );
    }

}

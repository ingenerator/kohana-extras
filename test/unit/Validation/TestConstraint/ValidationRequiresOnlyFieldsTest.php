<?php


namespace test\unit\Validation\TestConstraint;


use Ingenerator\KohanaExtras\Validation\TestConstraint\ValidationRequiresOnlyFields;
use PHPUnit\Framework\TestCase;

class ValidationRequiresOnlyFieldsTest extends TestCase
{

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            ValidationRequiresOnlyFields::class,
            new ValidationRequiresOnlyFields(['foo', 'bar'])
        );
    }
}
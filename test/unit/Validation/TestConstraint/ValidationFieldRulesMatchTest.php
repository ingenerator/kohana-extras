<?php


namespace test\unit\Validation\TestConstraint;


use Ingenerator\KohanaExtras\Validation\TestConstraint\ValidationFieldRulesMatch;
use PHPUnit\Framework\TestCase;

class ValidationFieldRulesMatchTest extends TestCase
{

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            ValidationFieldRulesMatch::class,
            new ValidationFieldRulesMatch('foo', ['bar'])
        );
    }
}
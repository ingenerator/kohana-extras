<?php


namespace test\unit\Validation\TestConstraint;


use Ingenerator\KohanaExtras\Validation\TestConstraint\ValidationRulesMatch;
use PHPUnit\Framework\TestCase;

class ValidationRulesMatchTest extends TestCase
{

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            ValidationRulesMatch::class,
            new ValidationRulesMatch(['foo'])
        );
    }
}
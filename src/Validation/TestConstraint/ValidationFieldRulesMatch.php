<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\Validation\TestConstraint;


use Ingenerator\KohanaExtras\Validation\ImmutableKohanaValidation;
use Ingenerator\KohanaExtras\Validation\TestConstraint\BaseValidationConstraint;

/**
 * Asserts that all rules for a single field exactly matches the expectation
 *
 * @package test\assert\ValidationConstraint
 */
class ValidationFieldRulesMatch extends BaseValidationConstraint
{

    /**
     * @var string
     */
    protected $expect_rules;

    /**
     * @var string
     */
    protected $field;

    /**
     * @param array $expect_rules
     */
    public function __construct($field, array $expect_rules)
    {
        parent::__construct();
        $this->field        = $field;
        $this->expect_rules = $expect_rules;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateValidation(ImmutableKohanaValidation $other, $description)
    {
        $rule_list = \Arr::get($this->exportValidationRules($other), $this->field, []);

        if ($this->expect_rules === $rule_list) {
            return TRUE;
        }

        try {
            $this->assertArraysEqual($this->expect_rules, $rule_list);
        } catch (\SebastianBergmann\Comparator\ComparisonFailure $failure) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                trim($description."\n".$this->failureDescription($other)),
                $failure
            );
        }

        return TRUE;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return sprintf(
            'has these rules for field `%s`: %s',
            $this->field,
            $this->exporter->export($this->expect_rules)
        );
    }


}

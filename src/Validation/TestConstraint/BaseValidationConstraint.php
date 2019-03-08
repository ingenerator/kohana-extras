<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\Validation\TestConstraint;


use Ingenerator\KohanaExtras\Validation\ImmutableKohanaValidation;
use Ingenerator\PHPUtils\Object\ObjectPropertyRipper;

if ( ! class_exists('\PHPUnit\Framework\Constraint\Constraint')) {
    // This is a temporary monkey-patch to support both PHPUnit 4.x and 7.x ahead of dropping
    // support for php5
    class_alias('PHPUnit_Framework_Constraint', '\PHPUnit\Framework\Constraint\Constraint');
}

/**
 * Base class for making custom assertions on validation objects
 *
 * @package test\assert\ValidationConstraint
 */
abstract class BaseValidationConstraint extends \PHPUnit\Framework\Constraint\Constraint
{

    /**
     * {@inheritdoc}
     */
    public function evaluate($other, $description = '', $returnResult = FALSE)
    {
        if ( ! $other instanceof ImmutableKohanaValidation) {
            $this->fail($other, 'Value should be an instance of '.ImmutableKohanaValidation::class);
        }

        try {
            return $this->evaluateValidation($other, $description);
        } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
            if ($returnResult) {
                return FALSE;
            }
            throw $e;
        }
    }

    /**
     * Implement the comparison for this constraint
     *
     * @param ImmutableKohanaValidation $other
     * @param string                    $description
     *
     * @return bool
     */
    abstract protected function evaluateValidation(ImmutableKohanaValidation $other, $description);

    /**
     * {@inheritdoc}
     */
    protected function failureDescription($other): string
    {
        if ($other instanceof ImmutableKohanaValidation) {
            $export = 'An instance of '.get_class($other).' with rules: '.$this->exporter->export(
                    $this->exportValidationRules($other)
                );
        } else {
            $export = $this->exporter->export($other);
        }

        return $export.' '.$this->toString();
    }

    /**
     * Reformat the validation object's rules objects into a format that makes sense for assertions
     *
     * @param $other
     *
     * @return array
     */
    protected function exportValidationRules(ImmutableKohanaValidation $other)
    {
        $rules = ObjectPropertyRipper::ripOne($other, '_rules');

        $rule_list = [];
        foreach ($rules as $field => $field_rules) {
            foreach ($field_rules as $index => $field_rule) {
                $rule_name = array_shift($field_rule);
                if (isset($rule_list[$field][$rule_name])) {
                    throw new \UnexpectedValueException(
                        "Unexpected multiple `$rule_name` rules on field `$field`"
                    );
                }
                $rule_list[$field][$rule_name] = array_shift($field_rule);
            }
        }

        return $rule_list;
    }

    /**
     * @param array $expected
     * @param array $actual
     *
     * @throws \SebastianBergmann\Comparator\ComparisonFailure
     */
    protected function assertArraysEqual(array $expected, array $actual)
    {
        $comparatorFactory = \SebastianBergmann\Comparator\Factory::getInstance();
        $comparator        = $comparatorFactory->getComparatorFor($expected, $actual);
        $comparator->assertEquals($expected, $actual);
    }


}

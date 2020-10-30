<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\Validation\TestConstraint;


use Ingenerator\KohanaExtras\Validation\ImmutableKohanaValidation;
use Ingenerator\PHPUtils\Object\ObjectPropertyRipper;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory;


/**
 * Base class for making custom assertions on validation objects
 *
 * @package test\assert\ValidationConstraint
 */
abstract class BaseValidationConstraint extends Constraint
{

    /**
     * {@inheritdoc}
     */
    public function evaluate($other, $description = '', $returnResult = FALSE): ?bool
    {
        if ( ! $other instanceof ImmutableKohanaValidation) {
            $this->fail($other, 'Value should be an instance of '.ImmutableKohanaValidation::class);
        }

        try {
            return $this->evaluateValidation($other, $description);
        } catch (ExpectationFailedException $e) {
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
            $export = 'An instance of '.\get_class($other).' with rules: '.$this->exporter()->export(
                    $this->exportValidationRules($other)
                );
        } else {
            $export = $this->exporter()->export($other);
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
                $rule_name = \array_shift($field_rule);
                if (isset($rule_list[$field][$rule_name])) {
                    throw new \UnexpectedValueException(
                        "Unexpected multiple `$rule_name` rules on field `$field`"
                    );
                }
                $rule_list[$field][$rule_name] = \array_shift($field_rule);
            }
        }

        return $rule_list;
    }

    /**
     * @param array $expected
     * @param array $actual
     *
     * @throws ComparisonFailure
     */
    protected function assertArraysEqual(array $expected, array $actual)
    {
        $comparatorFactory = Factory::getInstance();
        $comparator        = $comparatorFactory->getComparatorFor($expected, $actual);
        $comparator->assertEquals($expected, $actual);
    }


}

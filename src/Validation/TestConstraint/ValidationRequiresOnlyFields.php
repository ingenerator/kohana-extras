<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\Validation\TestConstraint;


use Ingenerator\KohanaExtras\Validation\ImmutableKohanaValidation;
use Ingenerator\KohanaExtras\Validation\TestConstraint\BaseValidationConstraint;

/**
 * Asserts that only the specified fields are required
 *
 * @package test\assert\ValidationConstraint
 */
class ValidationRequiresOnlyFields extends BaseValidationConstraint
{

    /**
     * @var string[]
     */
    protected $expect_required;

    /**
     * @param string[] $expect_required
     */
    public function __construct(array $expect_required)
    {
        parent::__construct();
        sort($expect_required);

        $this->expect_required = $expect_required;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateValidation(ImmutableKohanaValidation $other, $description)
    {
        $rule_list = $this->exportValidationRules($other);
        $required  = [];
        foreach ($rule_list as $field => $field_rules) {
            if ( ! isset($field_rules['not_empty'])) {
                continue;
            }

            if ($field_rules['not_empty'] !== [':value']) {
                throw new \InvalidArgumentException(
                    'Params for `not_empty` rule of `'.$field.'` were not valid'.json_encode(
                        $field_rules
                    )
                );
            }
            $required[] = $field;
        }

        sort($required);
        try {
            $this->assertArraysEqual($this->expect_required, $required);
        } catch (\SebastianBergmann\Comparator\ComparisonFailure $failure) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
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
            'requires only these fields to be not_empty: %s',
            $this->exporter->export($this->expect_required)
        );
    }

}

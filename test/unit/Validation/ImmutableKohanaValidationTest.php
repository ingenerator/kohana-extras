<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\Validation;


use BadMethodCallException;
use Ingenerator\KohanaExtras\Validation\ImmutableKohanaValidation;
use Ingenerator\KohanaExtras\Validation\ValidationResult;

class ImmutableKohanaValidationTest extends \PHPUnit_Framework_TestCase
{

    public function test_it_is_initialisable()
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(ImmutableKohanaValidation::class, $subject);
        $this->assertInstanceOf(ValidationResult::class, $subject);
        $this->assertInstanceOf(\Validation::class, $subject);
    }

    public function test_it_is_valid_when_validation_succeeds()
    {
        $subject = $this->newSubject();
        $subject->check();
        $this->assertTrue($subject->isValid());
    }

    public function test_it_is_not_valid_when_validation_fails()
    {
        $subject = $this->newSubject();
        $subject->rule('anything', 'not_empty');
        $subject->check();
        $this->assertFalse($subject->isValid());
    }

    public function test_it_has_no_errors_when_validation_succeeds()
    {
        $subject = $this->newSubject();
        $subject->check();
        $this->assertSame([], $subject->errors());
    }

    public function test_it_has_errors_when_validation_fails()
    {
        $subject = $this->newSubject();
        $subject->rule('anything', 'not_empty');
        $subject->check();
        $this->assertArrayHasKey('anything', $subject->errors());
    }

    public function test_it_checks_on_first_isvalid_call_if_required()
    {
        $subject = $this->newSubject();
        $subject->rule('anything', 'not_empty');
        $this->assertFalse($subject->isValid());
    }

    public function test_it_checks_on_first_errors_call_if_required()
    {
        $subject = $this->newSubject();
        $subject->rule('anything', 'not_empty');
        $this->assertArrayHasKey('anything', $subject->errors());
    }

    public function provider_immutable_method_calls()
    {
        return [
            ['check', []],
            ['label', ['field', 'label']],
            ['labels', [['field', 'label']]],
            ['rule', ['field', 'not_empty', []]],
            ['rules', ['field', [['not_empty', []]]]],
            ['bind', [':thing', ['not_empty']]],
            ['error', ['field', 'wrong']],
        ];
    }

    /**
     * @dataProvider      provider_immutable_method_calls
     * @expectedException BadMethodCallException
     */
    public function test_it_throws_for_any_attempted_modification_after_check($method, $args)
    {
        $subject = $this->newSubject();
        $subject->check();
        call_user_func_array([$subject, $method], $args);
    }

    /**
     * @dataProvider      provider_immutable_method_calls
     */
    public function test_it_does_not_throw_for_modification_before_check($method, $args)
    {
        $subject = $this->newSubject();
        call_user_func_array([$subject, $method], $args);
    }

    public function provider_object_values()
    {
        return [
            [new \DateTimeImmutable],
            [new \stdClass],
        ];
    }

    /**
     * @dataProvider provider_object_values
     */
    public function test_it_can_provide_error_messages_when_data_includes_object_values($value)
    {
        $subject = $this->newSubject(['field' => new $value]);
        $subject->rule('field', 'equals', [':value', 'not-this']);
        $errors = $subject->errors('validation');
        $this->assertInternalType('string', $errors['field']);
    }

    public function newSubject($data = [])
    {
        return new \Ingenerator\KohanaExtras\Validation\ImmutableKohanaValidation($data);
    }

}

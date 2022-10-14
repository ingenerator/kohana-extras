<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\Validation;

use Ingenerator\KohanaExtras\Validation\ValidationResult;

/**
 * Wraps a Kohana Validation object to prevent it being modified once it has been checked,
 * essentially so we can pretend that the validation object has been split into two - one
 * that does the validation and one that just carries the result. Generally this class
 * should never actually be used outside a validator - typehint instead on the
 * ValidationResult interface to reduce the risk of accidentally calling unexpected methods.
 *
 * @package Ingenerator\Validation
 */
class ImmutableKohanaValidation extends \Validation implements ValidationResult
{

    /**
     * @var bool
     */
    protected $checked;

    /**
     * @var bool flag used by offsetGet to determine whether to return raw or string value
     */
    protected $is_building_errors = FALSE;

    /**
     * @return bool
     */
    public function isValid()
    {
        return ! (bool) $this->errors();
    }

    /**
     * {@inheritdoc}
     */
    public function errors($file = NULL, $translate = TRUE)
    {
        if ( ! $this->checked) {
            $this->check();
        }

        $this->is_building_errors = TRUE;
        try {
            return parent::errors($file, $translate);
        } finally {
            $this->is_building_errors = FALSE;
        }
    }

    /**
     * Unpleasant workaround for the fact Kohana's default validation message building throws if
     * the validated values contain objects that cannot be converted to string - even if the value
     * is never used in the message itself.
     *
     * This getter provides a stringified version of the value during message building, and the raw
     * value when validating.
     *
     * {@inheritdoc}
     */
    public function offsetGet($offset): mixed
    {
        $value = parent::offsetGet($offset);
        if ($this->is_building_errors AND \is_object($value)) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d H:i:s');
            } elseif (\method_exists($value, '__toString')) {
                return (string) $value;
            } else {
                return '{object}';
            }
        } else {
            return $value;
        }
    }

    /**
     * @return bool
     */
    public function check()
    {
        $this->throwIfAlreadyChecked(__METHOD__);

        $result        = parent::check();
        $this->checked = TRUE;

        return $result;
    }

    /**
     * @param array $array
     *
     * @return static
     */
    public function copy(array $array)
    {
        return parent::copy($array);
    }

    /**
     * {@inheritdoc}
     */
    public function label($field, $label)
    {
        $this->throwIfAlreadyChecked(__METHOD__);

        return parent::label($field, $label);
    }

    /**
     * @param string $method
     */
    protected function throwIfAlreadyChecked($method)
    {
        if ($this->checked) {
            throw new \BadMethodCallException($method.' must not be called after validation has been checked');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function labels(array $labels)
    {
        $this->throwIfAlreadyChecked(__METHOD__);

        return parent::labels($labels);
    }

    /**
     * {@inheritdoc}
     */
    public function rule($field, $rule, array $params = NULL)
    {
        $this->throwIfAlreadyChecked(__METHOD__);

        return parent::rule($field, $rule, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function rules($field, array $rules)
    {
        $this->throwIfAlreadyChecked(__METHOD__);

        return parent::rules($field, $rules);
    }

    /**
     * {@inheritdoc}
     */
    public function bind($key, $value = NULL)
    {
        $this->throwIfAlreadyChecked(__METHOD__);

        return parent::bind($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function error($field, $error, array $params = NULL)
    {
        $this->throwIfAlreadyChecked(__METHOD__);

        return parent::error($field, $error, $params);
    }

}

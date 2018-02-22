<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\Validation\Mocks;


use Ingenerator\KohanaExtras\Validation\ImmutableKohanaValidation;
use Ingenerator\KohanaExtras\Validation\ValidationResult;
use Ingenerator\KohanaExtras\Validation\Validator;

class NeverOKValidatorStub implements Validator{

    /**
     * @param mixed[] $data
     *
     * @return ValidationResult
     */
    public function validate($data)
    {
        $data['_stub_never_ok_validator'] = '';
        $validation = new ImmutableKohanaValidation($data);
        $validation->rule('_stub_never_ok_validator', 'not_empty');
        $validation->check();

        return $validation;
    }

}

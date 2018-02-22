<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\Validation\Mocks;


use Ingenerator\KohanaExtras\Validation\ImmutableKohanaValidation;
use Ingenerator\KohanaExtras\Validation\ValidationResult;
use Ingenerator\KohanaExtras\Validation\Validator;

class AlwaysOKValidatorStub implements Validator {

    /**
     * @param mixed[] $data
     *
     * @return \Ingenerator\KohanaExtras\Validation\ValidationResult
     */
    public function validate($data)
    {
        $validation = new ImmutableKohanaValidation($data);
        $validation->check();
        return $validation;
    }

}

<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\Validation;


use Ingenerator\KohanaExtras\Validation\ValidationResult;

interface Validator {

    /**
     * @param mixed[] $data
     *
     * @return ValidationResult
     */
    public function validate($data);

}

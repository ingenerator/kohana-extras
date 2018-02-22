<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\Validation;

interface ValidationResult
{

    /**
     * @return bool
     */
    public function isValid();

    /**
     * Returns the error messages. If no file is specified, the error message
     * will be the name of the rule that failed. When a file is specified, the
     * message will be loaded from "field/rule", or if no rule-specific message
     * exists, "field/default" will be used. If neither is set, the returned
     * message will be "file/field/rule".
     *
     * By default all messages are translated using the default language.
     * A string can be used as the second parameter to specified the language
     * that the message was written in.
     *
     *     // Get errors from messages/forms/login.php
     *     $errors = $Validation->errors('forms/login');
     *
     * @uses    Kohana::message
     *
     * @param   string $file      file to load error messages from
     * @param   mixed  $translate translate the message
     *
     * @return  array
     * @see     \Kohana_Validation::errors
     */
    public function errors($file = NULL, $translate = TRUE);

    /**
     * @return mixed[]
     */
    public function data();

}

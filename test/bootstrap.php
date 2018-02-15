<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @copyright 2017 inGenerator Ltd
 */

$_SERVER['KOHANA_ENV'] = 'DEVELOPMENT';
require_once(__DIR__.'/../koharness_bootstrap.php');

// Require fake session implementation from Koharness to avoid errors in unit tests
require_once __DIR__.'/../vendor/kohana/koharness/helper_classes/Session/Fake.php';
\Session::$default = 'fake';

\Session::$instances['fake'] = new Session_Fake;

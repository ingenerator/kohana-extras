<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @copyright 2017 inGenerator Ltd
 */

$_SERVER['KOHANA_ENV'] = 'DEVELOPMENT';
require_once(__DIR__.'/../koharness_bootstrap.php');

//use test\mock\Ingenerator\Warden\UI\Kohana\Session\MockSession;
//

//require_once(__DIR__.'/../application/bootstrap.php');
//
//// Autoload mocks and test-support helpers that should not autoload in the main app
//$mock_loader = new \Composer\Autoload\ClassLoader;
//$mock_loader->addPsr4('test\\mock\\', [__DIR__.'/mock/']);
//$mock_loader->addPsr4('test\\assert\\', [__DIR__.'/assert/']);
//$mock_loader->addPsr4('test\\unit\\', [__DIR__.'/unit/']);
//$mock_loader->register();
//
//
//// To prevent errors accessing session data from the dependencies unit tests
//\Session::$default           = 'mock';
//\Session::$instances['mock'] = new MockSession();

<?php

use Composer\Autoload\ClassLoader;
use Doctrine\DBAL\Exception\ConnectionException;

// Set up temporary directories for the app
// These need to exist and be writable, though we do very little with them
(function () {
    $mkdir = function (string $dir): string {
        if ( ! mkdir($dir, recursive: TRUE)) {
            throw new RuntimeException('Could not create temporary directory in '.$dir);
        }

        return $dir;
    };

    $basedir = uniqid(sys_get_temp_dir().'/kohana-tests/');
    define('APPPATH', $mkdir($basedir.'/application').'/');
    $mkdir(APPPATH.'cache');
    define('MODPATH', $mkdir($basedir.'/modules').'/');
    define('DOCROOT', $mkdir($basedir.'/htdocs').'/');
})();

define('SYSPATH', __DIR__.'/../vendor/ingenerator/kohana-core/');
define('EXT', '.php');

error_reporting(E_ALL);

define('KOHANA_START_TIME', microtime(TRUE));
define('KOHANA_START_MEMORY', memory_get_usage());

// Load the core Kohana class and extension
require SYSPATH.'classes/Kohana/Core'.EXT;
require SYSPATH.'classes/Kohana'.EXT;

// Set default runtime environment (based on default Kohana install)
date_default_timezone_set('America/Chicago');
setlocale(LC_ALL, 'en_US.utf-8');
mb_substitute_character('none');

// Enable the Kohana auto-loader and enable it for unserialization
spl_autoload_register(['Kohana', 'auto_load']);
ini_set('unserialize_callback_func', 'spl_autoload_call');

// Configure and initialize Kohana
I18n::lang('en-us');
Kohana::init();
Kohana::$config->attach(new Config_File);

Kohana::modules([]);

//// Hacky workaround to show a simple text exception on fatal errors
//// Otherwise Kohana's shutdown function catches it and shows a huge HTML trace that's horrible to follow
//\file_put_contents(
//    APPPATH.'/views/text-error.php',
//    '<?php echo "\n\nUnhandled error: ".\Kohana_Exception::text($e)."\n";'
//);
//Kohana_Exception::$error_view = 'text-error';
//
//// Require fake session implementation from Koharness to avoid errors in unit tests
//require_once __DIR__.'/../vendor/kohana/koharness/helper_classes/Session/Fake.php';
//\Session::$default           = 'fake';
//\Session::$instances['fake'] = new Session_Fake;

// Autoload mocks and test-support helpers that should not autoload in the main app
$mock_loader = new ClassLoader;
$mock_loader->addPsr4('test\\mock\\Ingenerator\\KohanaExtras\\', [__DIR__.'/mock/']);
$mock_loader->addPsr4('test\\unit\\Ingenerator\\KohanaExtras\\', [__DIR__.'/unit/']);
$mock_loader->register();

// Fake the Doctrine exception class that we use for our handler without having to require doctrine here
if ( ! class_exists(ConnectionException::class)) {
    require_once __DIR__.'/mock/mock_doctrine_connection_exception.php';
}

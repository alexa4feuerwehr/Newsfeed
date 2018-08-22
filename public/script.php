<?php
use Zend\Console\Console;
use ZF\Console\Application;
use ZF\Console\Dispatcher;
use Zend\Stdlib\ArrayUtils;
use Zend\ServiceManager\ServiceManager;

//
chdir(dirname(__DIR__));

//
ini_set('memory_limit', '8096M');

//
require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoloader

/**
 * @todo: this should be done in one wa with the default index.php
 */
// Retrieve configuration
$appConfig = require __DIR__ . '/../config/application.config.php';


/**
 * @this should may be not done here
 */
$smConfig = new Zend\Mvc\Service\ServiceManagerConfig([]);
$serviceManager = new ServiceManager();
$smConfig->configureServiceManager($serviceManager);
$serviceManager->setService('ApplicationConfig', $appConfig);
$serviceManager->get('ModuleManager')->loadModules();

// clear console before run application
Console::getInstance()->clear();


$dispatcher = new Dispatcher();

/**
 * @example php script.php ProcessInQueue --limit=555
 *
 * @see https://mateusztymek.pl/blog/zf-console-php-microframework-for-console-applications
 *
 * @see https://github.com/zfcampus/zf-console
 */


//
$application = new Application(
    'Intires Webapp Console',
    '1.0.0',
    [
        /*
        [
            'name' => 'test',
            'route' => '<package> [--target=]',
            'description' => 'Build a package, using <package> as the package filename, and --target as the application directory to be packaged.',
            'short_description' => 'Build a package',
            'options_descriptions' => [
                '<package>' => 'Package filename to build',
                '--target'  => 'Name of the application directory to package; defaults to current working directory',
            ],
            'defaults' => [
                'target' => getcwd(), // default to current working directory
            ],
            'handler' => \IntiresCore\Console\ProcessInQueueController::class,
        ],
        */
        [
            'name'      => 'ImportQuestions',
            'route'     => '[--limit=]',
            'handler'   => \Newsfeed\Console\ImportQuestions::class,
        ],
    ],
    Console::getInstance(),
    $dispatcher
);

$exit = $application->run();
exit($exit);
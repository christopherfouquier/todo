<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add("app", dirname(__DIR__));

$app = new Silex\Application();

/* Function debug */
$app['debug'] = true;

/* Use component */
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

/* Initialize vendors */
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views/',
    'twig.options' => array('cache' => __DIR__.'/../cache'),
));

/**
 * Translations
 */
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale' => 'fr',
    'locale_fallback' => 'fr',
));

/**
 * Database (Propel)
 */
$app->register(new Propel\Silex\PropelServiceProvider(), array(
    'propel.config_file' => __DIR__ . '/config/propel/todo-conf.php',
    'propel.model_path'  => __DIR__ . '/model',
    'propel.internal_autoload' => true,
));

/**
 * Url generator
 */
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

/**
 * Forms
 */
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());

/**
 * Sessions
 */
$app->register(new Silex\Provider\SessionServiceProvider());

/**
 * Security
 */
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'main' => array(
            'anonymous' => true,
            'pattern' => '^/',
            'form'   => array('login_path' => '/', 'check_path' => '/login_check'),
            'logout' => array('logout_path' => '/logout'),
            'users'  => $app->share(function () use ($app) {
                    return new app\Provider\UserProvider();
                })
        )
    ),
    'security.access_rules' => array(
        array('^/manager', 'ROLE_USER'),
    ),
    'security.encoder.digest' => $app->share(function() use ($app) {
        //return new MessageDigestPasswordEncoder('sha1', false, 1);
        return new PlaintextPasswordEncoder();
    })
));

if ($app['debug']) {
	/**
     * Whoops
     */
    $app->register(new Whoops\Provider\Silex\WhoopsServiceProvider);
}

$app->boot();

return $app;
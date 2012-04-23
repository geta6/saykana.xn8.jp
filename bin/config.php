<?php

// Debug
$app['debug']  = true;

// Puny
$app['puny'] = $app->share(function () {
  include_once 'Net/IDNA.php';
  return Net_IDNA::getInstance();
});

// Vars
$app['host']       = $app['puny']->decode($_SERVER['HTTP_HOST']);
$app['mobile']     = preg_match('/mobile/i', $_SERVER['HTTP_USER_AGENT']);
$app['domain']     = implode('.', array_slice(explode('.', $app['host']), -2));
$app['subdomain']  = implode('.', array_slice(explode('.', $app['host']), 0, -2));
$app['object']     = implode('',  array_slice(explode('.', $app['host']), 0, -1));

// Root Dirs
$app['root.vendor']  = __DIR__ . '/../lib';
$app['root.source']  = __DIR__ . '/../src';
$app['root.cache']   = __DIR__ . '/../tmp';
$app['root.views']   = __DIR__ . '/../var';
$app['root.web']     = __DIR__ . '/../web';

// Class Path
$app['autoloader']->registerNamespace('Symfony', $app['root.vendor'] . '/assetic/vendor/symfony/process');
$app['autoloader']->registerNamespace('SilexExtension', $app['root.vendor'] . '/extension/src');

// Twig
$app['twig.class_path']  = $app['root.vendor'] . '/twig/lib/';
$app['twig.cache_dir']   = $app['root.cache'] . '/twig';
$app['twig.path']        = $app['root.views'];

// Http
$app['http_cache.cache_dir']  = $app['root.cache'] . '/http';

// Secrets
$etc = require_once __DIR__ . '/secret.php';

// Database
$app['pdo.options']  = array(
  'driver'    => 'mysql',
  'dbname'    => $etc['dbname'],
  'host'      => 'localhost',
  'user'      => $etc['user'],
  'password'  => $etc['password'],
  'debug'     => false,
);

// Apc
$app['apc.options']  = array(
  'ttl'    => 0,
  'prefix' => 'todo.',
);

// Assetic
$app['assetic.class_path']    = $app['root.vendor'] . '/assetic/src';
$app['assetic.path_to_web']   = $app['root.web'] . '/src';
$app['assetic.cache_path']    = $app['root.cache'] . '/assetic';

$app['assetic.path_to_yui']   = '/usr/share/yui-compressor/yui-compressor.jar';
$app['assetic.path_to_node']  = '/usr/bin/node';

$app['assetic.input_css']     = $app['root.source'] . '/css/*.css';
$app['assetic.input_less']    = $app['root.source'] . '/css/*.less';
$app['assetic.input_js']      = $app['root.source'] . '/js/*.js';
$app['assetic.input_mb']      = $app['root.source'] . '/mb/*.less';

$app['assetic.output_css']    = 'styles.css';
$app['assetic.output_js']     = 'scripts.js';
$app['assetic.output_mb']     = 'styles.mb.css';


// Mecab
$app['mecab'] = $app->share(function () {
  return new MeCab_Tagger();
});

// Wav Log
$app['wav.cache_dir'] = $app['root.cache'] . '/wav';

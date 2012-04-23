<?php

namespace SilexExtension;

use Silex\Application;
use Silex\ServiceProviderInterface;
use PDO;

class PdoProvider implements ServiceProviderInterface
{
  public function register(Application $app)
  {
    $app['pdo.options'] = array_replace(array(
      'dbname'    => null,
      'driver'    => 'mysql',
      'host'      => 'localhost',
      'user'      => 'root',
      'password'  => null,
      'debug'     => false,
    ), isset($app['pdo.options']) ? $app['pdo.options'] : array());

    $app['pdo'] = $app->share(function () use ($app) {
      $opt = $app['pdo.options'];
      $pdo = new PDO("{$opt['driver']}:host={$opt['host']}; dbname={$opt['dbname']}", $opt['user'], $opt['password']);
      if ($opt['debug']) {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
      return $pdo;
    });
  }
}

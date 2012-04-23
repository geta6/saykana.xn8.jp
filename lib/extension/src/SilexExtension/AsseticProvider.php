<?php

namespace SilexExtension;

use Assetic\AssetManager;
use Assetic\FilterManager;
use Assetic\AssetWriter;
use Assetic\Asset\AssetCache;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\LazyAssetManager;
use Assetic\Cache\FilesystemCache;
use Assetic\Extension\Twig\AsseticExtension as TwigAsseticExtension;

use Silex\Application;
use Silex\ServiceProviderInterface;

class AsseticProvider implements ServiceProviderInterface
{
  public function register(Application $app)
  {
    $app['assetic.options'] = array_replace(array(
      'debug' => false,
      'formulae_cache_dir' => null,
    ), isset($app['assetic.options']) ? $app['assetic.options'] : array());

    $app['assetic'] = $app->share(function () use ($app) {
      if (isset($app['assetic.formulae']) &&
        !is_array($app['assetic.formulae']) &&
        !empty($app['assetic.formulae'])
      ) {
        $app['assetic.lazy_asset_manager'];
      }

      return $app['assetic.factory'];
    });

    $app['assetic.factory'] = $app->share(function() use ($app) {
      $options = $app['assetic.options'];
      $factory = new AssetFactory($app['assetic.path_to_web'], $options['debug']);
      $factory->setAssetManager($app['assetic.asset_manager']);
      $factory->setFilterManager($app['assetic.filter_manager']);
      return $factory;
    });

    $app->after(function() use ($app) {
      $app['assetic.asset_writer']->writeManagerAssets(
        $app['assetic.lazy_asset_manager']);
      $app['assetic.asset_writer']->writeManagerAssets(
        $app['assetic.asset_manager']);
    });

    $app['assetic.asset_writer'] = $app->share(function () use ($app) {
      return new AssetWriter($app['assetic.path_to_web']);
    });

    $app['assetic.asset_manager'] = $app->share(function () use ($app) {
      $assets = isset($app['assetic.assets']) ? $app['assetic.assets'] : function() {};
      $manager = new AssetManager();

      call_user_func_array($assets, array($manager, $app['assetic.filter_manager']));
      return $manager;
    });

    $app['assetic.filter_manager'] = $app->share(function () use ($app) {
      $filters = isset($app['assetic.filters']) ? $app['assetic.filters'] : function() {};
      $manager = new FilterManager();

      call_user_func_array($filters, array($manager));
      return $manager;
    });

    $app['assetic.lazy_asset_manager'] = $app->share(function () use ($app) {
      $formulae = isset($app['assetic.formulae']) ? $app['assetic.formulae'] : array();
      $options  = $app['assetic.options'];
      $lazy     = new LazyAssetmanager($app['assetic.factory']);

      if (empty($formulae)) {
        return $lazy;
      }

      foreach ($formulae as $name => $formula) {
        $lazy->setFormula($name, $formula);
      }

      if ($options['formulae_cache_dir'] !== null && $options['debug'] !== true) {
        foreach ($lazy->getNames() as $name) {
          $lazy->set($name, new AssetCache(
            $lazy->get($name),
            new FilesystemCache($options['formulae_cache_dir'])
          ));
        }
      }
      return $lazy;
    });

    $app->before(function () use ($app) {
      if (isset($app['twig'])) {
        $app['twig']->addExtension(new TwigAsseticExtension($app['assetic.factory']));
      }
    });

    if (isset($app['assetic.class_path'])) {
      $app['autoloader']->registerNamespace('Assetic', $app['assetic.class_path']);
    }
  }
}

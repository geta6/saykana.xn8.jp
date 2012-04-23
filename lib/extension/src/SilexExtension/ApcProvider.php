<?php

namespace SilexExtension;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ApcProvider implements ServiceProviderInterface
{
  public function register(Application $app)
  {
    $app['apc.options'] = array_replace(array(
      'ttl'     => 0,
      'prefix'  => null,
    ), isset($app['apc.options']) ? $app['apc.options'] : array());

    $app['apc'] = $app->share(function () use ($app) {
      $apc = new ApcInterface($app['apc.options']);
      return $apc;
    });
  }
}

class ApcInterface
{
  public $key;
  public $exist;
  public $ttl;
  private $prefix;

  public function __construct($opt)
  {
    $this->ttl = $opt['ttl'];
    $this->prefix = $opt['prefix'];
  }

  public function space($key)
  {
    $this->key   = $this->prefix.$key;
    $this->exist = apc_exists($key);
  }

  public function store($var, $ttl = false)
  {
    if (false === $ttl) $ttl = $this->ttl;
    $apc = apc_store($this->key, $var, $ttl);
    if ($apc) {
      return $var;
    } else {
      return false;
    }
  }

  public function fetch()
  {
    if (apc_exists($this->key)) {
      return apc_fetch($key);
    } else {
      return false;
    }
  }

  public function clear($key)
  {
    return apc_delete($key);
  }
}

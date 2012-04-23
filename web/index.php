<?php

$app = require_once __DIR__ . '/../bin/app.php';
require_once __DIR__ . '/../bin/routes.php';

if ($app['debug']) {
  $app->run();
} else {
  $app['http_cache']->run();
}

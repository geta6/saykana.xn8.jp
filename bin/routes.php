<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;




//--------------------------------------------------------------
$restrict = function (Request $req)
  {
    $tld = substr($req->server->get('HTTP_HOST'), -2);
    $end = mb_substr($req->get('query'), -2);
    if ('jp' != $tld || ('タイ' != $end && 'ナイ' != $end)) {
      return new Response('Query string must be end with タイ or ナイ.', 403);
    }
  };




//--------------------------------------------------------------
$app->match('/', function (Request $req) use ($app)
    {
      $query = '';

      for ($node = $app['mecab']->parseToNode($app['object']); $node; $node = $node->getNext()) {
        if (2 != $node->getStat() && 3 != $node->getStat()) {
          $surf = $node->getSurface();
          $feat = explode(',', $node->getFeature());
          $temp = trim(array_pop($feat));
          if ('*' == $temp) {
            $temp = $surf;
            if (preg_match('/[a-zA-Z]+/', $surf)) {
              $lang = (array)simplexml_load_file("http://aikelab.net/mikugo/api.cgi?sentence=$surf");
              $temp = trim($lang[0]);
            }
          }
          $query.= $temp;
        }
      }

      $stm = $app['pdo']->prepare('
        INSERT INTO たい (fqdn, query, date)
        VALUES (:fqdn, :query, FROM_UNIXTIME(:date))
        ON DUPLICATE KEY UPDATE times = times + 1');
      $stm->execute(array(
        'fqdn'  => $app['host'],
        'query' => $query,
        'date'  => time()
      ));

      $thumb = $app['root.web'] . '/src/thumb/' . $query . '.png';
      if (!file_exists($thumb)) {
        $thumb = '';
      }

      $stm = $app['pdo']->query('SELECT * FROM たい ORDER BY last DESC');
      return $app['twig']->render('index.twig', array(
        'stm'   => $stm->fetchAll(PDO::FETCH_ASSOC),
        'query' => $query,
        'thumb' => $thumb
      ));
    });




//--------------------------------------------------------------
$app->match('/redirect', function (Request $req) use ($app)
      {
        $uri = 'http://' . $req->get('query') . '.たい.jp';
        $uri = $app['puny']->encode($uri);
        return $app->redirect($uri);
      });




//--------------------------------------------------------------
$app->match('/say/{query}', function (Request $req, $query) use ($app)
      {

        if (!file_exists($app['wav.cache_dir'])) {
          mkdir($app['wav.cache_dir']);
        }
        $status_code = '200';
        $wave_file   = $app['wav.cache_dir'] . "/$query.wav";
        if (!is_file($wave_file)) {
          $status_code = '201';
          file_put_contents($wave_file, file_get_contents("http://192.168.0.72/~geta/index.php?query=$query"));
        }

        return $app->stream(function () use ($wave_file) {
          $size = filesize($wave_file);
          if (!isset($_SERVER['HTTP_RANGE'])) {
            header("Content-Length: $size");
            echo file_get_contents($wave_file);
          } else {
            $file = fopen($wave_file, 'rb');
            list($tos, $pan) = explode('=', $_SERVER['HTTP_RANGE']);
            list($ini, $end) = explode('-', $pan);
            header('HTTP/1.1 206 Partial Content');
            header('Content-Length: ' . ($end - $ini + 1));
            header("Content-Range: bytes $pan/$size");
            fseek($file, $ini);
            @ob_end_clean();
            while (!feof($file) && 0 == connection_status() && !connection_aborted()) {
              set_time_limit(0);
              echo fread($file, 8192);
              @flush();
              @ob_flush();
            }
            fclose($file);
          }
        }, $status_code, array(
          'Content-Type'  => 'audio/wave',
          'Accept-Ranges' => 'bytes'
        ));

      })->middleware($restrict);

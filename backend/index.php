<?php
/*
 * For OSX backend server.
 */


if (isset($_GET['query'])) {


  $query = $_GET['query'];
  $end   = mb_substr($query, -2);


  if ('タイ' != $end && 'ナイ' != $end) {

    header('HTTP', true, 403);
    exit();

  } else {

    $aif = "/tmp/$query.aif";
    $wav = "/tmp/$query.wav";

    exec("/usr/local/bin/SayKana -s 80 '$query' -o '$aif'");
    exec("/usr/local/bin/sox '$aif' -r 44100 -c 1 '$wav'");

    $size = filesize($wav);

    header("Content-Type: audio/x-wav");
    header('Accept-Ranges: bytes');

    if (!isset($_SERVER['HTTP_RANGE'])) {
      header("Content-Length: $size");
      echo file_get_contents($wav);
    } else {
      $file = fopen($wav, 'rb');
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

    unlink($aif);
    unlink($wav);

    exit('0');

  }


} else {


  header('HTTP', true, 400);
  exit();


}

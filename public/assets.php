<?php

  require_once('../../config/config.inc.php');

  $parts = explode('.', $_REQUEST['file']);
  $file_type = end($parts);
  $preview = strpos($_SERVER['REQUEST_URI'], 'preview=1') > 0;

  $path = '';
  if ($file_type == 'pdf' && $preview) {
    $path = 'assets/images/pdf.png';
  } else {
    $path = UPLOADS . str_replace('..', '', $_REQUEST['file']); // prevent reading from parent directories
  }

  $file = fopen($path, 'rb') or die("Unable to open file: '$path'");
  $size = filesize($path);

  header('Content-Type: ' . mime_content_type($path));
  header('Content-Length: ' . $size);
  header('Cache-Control: max-age=288000');

  fpassthru($file);

  fclose($file);

?>
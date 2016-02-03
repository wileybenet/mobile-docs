<?php

  require_once('../../config/config.inc.php');

  $file = fopen('template.htaccess', 'r') or die('Unable to open file!');
  $size = filesize('template.htaccess');
  $contents = fread($file, $size);
  fclose($file);

  $htaccess_data = [
    'dir' => (SUBDIR != '') ? (substr(SUBDIR, 1) . '/') : ''
  ];

  $htaccess_content = preg_replace_callback('/\{\{([^}]*)\}\}/', function($matches) use (&$htaccess_data) {
    return $htaccess_data[$matches[1]];
  }, $contents);

  if (file_exists('.htaccess')) {
    unlink('.htaccess');
  }

  $htaccess = fopen('.htaccess', 'w') or die('Unable to open file!');
  fwrite($htaccess, $htaccess_content);
  fclose($htaccess);

?>
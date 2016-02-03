<?php

  function get_args($preload = []) {
    global $argv;
    $args = [];
    foreach ($preload as $key) {
      $args[$key] = '';
    }
    $prev_key = '';
    foreach (array_slice($argv, 1) as $arg) {
      if (substr($arg, 0, 2) == '--') {
        $prev_key = substr($arg, 2);
      } else {
        $args[$prev_key] = $arg;
      }
    }

    return $args;
  }

  function write_file($path, $contents) {
    if (file_exists($path)) {
      unlink($path);
    }

    $new_file = fopen($path, 'w') or die('Unable to open file!');
    fwrite($new_file, $contents);
    fclose($new_file);
  }

  function render_template($template, $render, $data) {
    $file = fopen($template, 'r') or die('Unable to open file!');
    $size = filesize($template);
    $contents = fread($file, $size);
    fclose($file);

    $file_content = preg_replace_callback('/\{\{([^}]*)\}\}/', function($matches) use (&$data) {
      return $data[$matches[1]];
    }, $contents);

    if (file_exists($render)) {
      unlink($render);
    }

    $new_file = fopen($render, 'w') or die('Unable to open file!');
    fwrite($new_file, $file_content);
    fclose($new_file);
  }

?>
<?php

  class Upload {

    public static function read_file($name = 'file') {
      $file = fopen($_FILES[$name]['tmp_name'], 'r') or die('Unable to open file!');
      $size = filesize($_FILES['file']['tmp_name']);
      $contents = fread($file, $size);
      fclose($file);
      return [
        'name' => $_FILES['file']['name'],
        'type' => $_FILES['file']['type'],
        'contents' => $contents,
        'size' => $size
      ];
    }

    public static function read_image($name = 'file') {
      $file = fopen($_FILES[$name]['tmp_name'], 'rb') or die('Unable to open file!');
      $size = filesize($_FILES['file']['tmp_name']);
      return [
        'name' => $_FILES['file']['name'],
        'type' => $_FILES['file']['type'],
        'contents' => $file,
        'size' => $size
      ];
    }

  }


?>
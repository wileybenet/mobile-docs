<?php

  class Screenshot {

    /* @HTMLI
    *  returns: encoded array of images from general/screenshots
    */
    public static function random($context, $count) {
      $assets = [];
      $dir = UPLOADS . 'general/screenshots/';
      if (is_dir($dir)) {
        $files = (array) scandir($dir);
        $all = array_filter($files, function($path) {
          return $path[0] != '.';
        });

        foreach(array_slice($all, 0, $count) as $path) {
          if ($path[0] != '.') {
            $assets[] = ['src' => $path];
          }
        }
      }
      return str_replace('"', "'", json_encode($assets));
    }

  }

?>
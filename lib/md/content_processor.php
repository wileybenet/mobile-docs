<?php

  class ContentProcessor {

    private static $processes = [];

    public static function add_proc($cb_fn) {
      self::$processes[] = $cb_fn;
    }

    public static function pre() {
      ob_start('self::post');
    }

    public static function post($buffer = '') {

      if (Router::is_api_request()) {
        return $buffer;
      }

      // additional processes added by ctrls
      foreach (self::$processes as $proc) {
        $buffer = $proc($buffer);
      }

      // custom angular directives
      $buffer = preg_replace_callback("/data-(md-[a-z][^=\s>]*)/", function($matches) {
        $name = Filter::snake_to_camel($matches[1]);
        Render::include_script("app/$name");
        return $matches[0];
      }, $buffer);

      // assets
      $buffer = preg_replace_callback("/<--([^-]+)-->/", function($matches) {
        return Render::$matches[1]();
      }, $buffer);

      // href and src urls
      $buffer = preg_replace_callback('/="(\/[^"]*)/', function($matches) {
        return '="' . SUBDIR . $matches[1];
      }, $buffer);

      // css urls
      $buffer = preg_replace_callback("/\((\/[^)]*)/", function($matches) {
        return "(" . SUBDIR . $matches[1];
      }, $buffer);

      return $buffer;
    }

  }


?>
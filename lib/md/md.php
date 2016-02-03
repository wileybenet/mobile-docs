<?php

  class MD {

    /* @HTMLI
    *  params: local_file_name
    *  returns: absolute path to an uploaded asset (asset must be local to the current document)
    */
    public static function asset($context, $file) {
      return "/asset/{$context['_class']}/{$context['name']}/$file";
    }

    /* @HTMLI
    *  params: type_name, group_name, file_name
    *  returns: absolute path to an uploaded asset
    */
    public static function global_asset($context, $type = '', $group = '', $item = '') {
      return "/asset/$type/$group/$item";
    }

    /* @HTMLI
    *  params: local_file_name
    *  returns: contents of an uploaded asset (asset must be local to the current document)
    */
    public static function inject($context, $file) {
      $path = UPLOADS . "{$context['_class']}/{$context['name']}/$file";
      $content = file_get_contents($path);
      $content = preg_replace('/<.*?>/', '', $content);
      return $content;
    }

    /* @HTMLI
    *  params: property_name
    *  returns: string value of a REQUEST parameter
    */
    public static function request($context, $prop) {
      return $_REQUEST[$prop];
    }

    /* @HTMLI
    *  params: property_name
    *  returns: string value of a full link root
    */
    public static function http_root($context) {
      return 'http://' . $_SERVER['HTTP_HOST'] . SUBDIR . '/';
    }

  }

?>
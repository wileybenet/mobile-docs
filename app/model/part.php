<?php

  class Part extends Record {

    public static function read_by_params($params) {
      return self::read_part($params['part']);
    }

    public static function read_part($part) {
      $part = self::read(['*'], TRUE, ["part = ?", $part]);
      return self::post_process_fields($part);
    }

    public static function read_parts($params, $parts) {
      //echo '<pre>'; debug_print_backtrace(); echo '</pre>'; exit;
      $parts = explode(',', $parts);
      for ($i = 0; $i < count($parts); ++$i) {
        $parts[$i] = Database::sanitize($parts[$i]);
      }
      $parts = implode(',', $parts);
      $where = Session::is_admin() ? '' : 'AND private = 0';
      $res = Database::query("SELECT * FROM part WHERE part IN ($parts) $where;");
      $result = Record::post_process($res);
      for ($i = 0; $i < count($result); ++$i) {
        $result[$i] = self::post_process_fields($result[$i]);
      }
      return $result;
    }

    public static function get_parts() {
      $res = Database::query("SELECT * FROM part");
      $records = self::post_process($res);
      $parts = [];
      foreach ($records as $record) {
        $parts[$record['part']] = $record;
      }
      return $parts;
    }

    private static function post_process_fields($part) {
      $part['features_raw'] = str_replace('|', '; ', $part['features']);
      $features = explode('|', $part['features']);
      $part['features'] = [];
      for ($i = 0; $i < count($features); ++$i) {
        $part['features'][] = ['feature' => $features[$i]];
      }
      preg_replace_callback('/([^:]*):([^;]*);/', function($matches) use (&$part) {
        $part['html-' . $matches[1]] = $matches[2];
      }, $part['html'] . ';');
      //echo '<pre>'; print_r($part); echo '</pre>'; exit;
      return $part;
    }

  }

?>
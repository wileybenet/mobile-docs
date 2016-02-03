<?php // Database connection service

  class Database {
    static $mysql;
    static $error;

    // connect to MySQL using global configs
    public static function initialize() {
      if (!isset(self::$mysql)) {
        self::$mysql = new mysqli(MYSQL_HOST, MYSQL_UN, MYSQL_PW, MYSQL_DB, MYSQL_PORT);
        if (self::$mysql->connect_errno) {
          echo "Failed to connect to MySQL: (" . $this->mysql->connect_errno . ") " . self::$mysql->connect_error;
        }
        self::$mysql->set_charset('utf8');
      }
    }

    // sanitize a single string or all parameters of a sprintf function call
    public static function sanitize($inject_array, $key = FALSE) {
      $sanitized_str = "";
      if (is_array($inject_array)) {
        $template = $inject_array[0];
        $sanitized = [];
        foreach (array_slice($inject_array, 1) as $item) {
         $sanitized[] = self::format($item);
        }
        $sanitized_str = preg_replace_callback('/\?/', function($matches) use (&$sanitized) {
          return array_shift($sanitized);
        }, $template);
      } else {
        $sanitized_str = $key ? mysqli_real_escape_string(self::$mysql, trim($inject_array)) : self::format($inject_array);
      }
      return $sanitized_str;
    }

    // send sql query and echo query string if /?q
    public static function query($q_str) {
      if (isset($_GET['q']) && Session::$user['auth'] > 0) {
        echo $q_str . "<br />";
      }
      return self::$mysql->query($q_str);
    }

    public static function format($var) {
      $escaped_str = mysqli_real_escape_string(self::$mysql, trim($var));
      if (is_string($var)) {
        return "'" . $escaped_str . "'";
      } elseif (is_numeric($var)) {
        return $escaped_str;
      } elseif (is_null($var)) {
        return 'NULL';
      } else {
        return "'" . $escaped_str . "'";
      }
    }
  }

?>
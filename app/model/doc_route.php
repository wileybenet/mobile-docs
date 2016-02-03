<?php
  
  class DocRoute extends Record {

    protected static $props = [
      'selector' => 'CONCAT("GET", route) AS selector',
      'handler' => 'IF(ISNULL(handler), "View#doc", handler) AS handler'
    ];

    public static function read_all() {
      return self::read([self::$props['selector'], self::$props['handler']]);
    }

    public static function create_all($routes) {
      $sani_routes = [];
      foreach ($routes as $route) {
        $sani_routes[] = Database::sanitize(["(?, ?, ?)", $route['doc_id'], $route['route'], $route['handler']]);
      }
      $sani_routes = implode(', ', $sani_routes);
      return Database::query("INSERT INTO doc_route (`doc_id`, `route`, `handler`) VALUES $sani_routes");
    }

    public static function destroy_all($doc_id) {
      $q = Database::sanitize(["DELETE FROM doc_route WHERE doc_id = ?", $doc_id]);
      return Database::query($q);
    }

  }

?>
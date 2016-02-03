<?php

  class Doc extends Record {

    protected static $props = [
      'content' => "(SELECT content FROM doc_content WHERE doc_id = doc.id ORDER BY updated DESC LIMIT 1) AS content",
      'parent' => "(SELECT parent_doc_id FROM nav WHERE nav.doc_id = doc.id) AS parent_id",
      'order' => "(SELECT sort_order FROM nav WHERE nav.doc_id = doc.id) AS 'sort_order'",
      'routes' => "GROUP_CONCAT(CONCAT(doc_route.route, ' => ', IF(ISNULL(handler), 'View#doc', handler)) SEPARATOR ', ') AS 'routes'"
    ];

    public static function create_nav($model) {
      $doc = self::create(Record::allow($model, ['title', 'name', 'type']));
      $nav = Nav::create(['doc_id' => $doc['id']]);
      return $doc;
    }

    public static function read_content($id = FALSE) {
      $select = "
        doc.*,
        (SELECT content FROM doc_content WHERE doc_id = doc.id ORDER BY updated DESC LIMIT 1) AS 'content',
        (SELECT parent_doc_id FROM nav WHERE nav.doc_id = doc.id) AS 'parent_id',
        (SELECT sort_order FROM nav WHERE nav.doc_id = doc.id) AS 'sort_order',
        GROUP_CONCAT(CONCAT(doc_route.route, IF(ISNULL(handler), '', CONCAT(' => ', handler))) SEPARATOR ', ') AS 'routes'";
      $where = $id ? Database::sanitize(["WHERE doc.id = ?", $id]) : '';
      $res = Database::query("  
        SELECT $select
        FROM doc
        LEFT JOIN doc_route
          ON doc_route.doc_id = doc.id
        $where
        GROUP BY doc.id
        ORDER BY sort_order");
      $result = self::post_process($res);
      if ($id) {
        return isset($result[0]) ? $result[0] : [];
      } else {
        return $result;
      }
    }

    public static function by_name($name) {
      return parent::read(['doc.*', self::$props['content']], TRUE, ["name = ?", $name]);
    }

    public static function by_route($route) {
      $route = Database::sanitize($route);
      $content = self::$props['content'];
      $res = Database::query("SELECT doc.*, $content FROM doc INNER JOIN doc_route ON doc_route.doc_id = doc.id WHERE doc_route.route = $route LIMIT 1;");
      $result = self::post_process($res);
      return isset($result[0]) ? $result[0] : [];
    }

    public static function update($model, $id, $update_lit = '') {
      Session::permit_admin();

      $success = parent::update(Record::allow($model, ['title', 'name', 'type']), $id, $update_lit);
      if (isset($model['content'])) {
        $success = DocContent::create(['doc_id' => $id, 'content' => $model['content']]);
      }
      if (isset($model['routes'])) {
        DocRoute::destroy_all($id);
        if ($model['routes']) {
          $routes = [];
          foreach (explode(',', $model['routes']) as $route_def) {
            $parts = explode('=>', $route_def);
            $route = trim($parts[0]);
            if (strlen($route) == 0 || $route[0] != '/') {
              $route = '/' . $route;
            }
            $handler = count($parts) > 1 ? trim($parts[1]) : null;
            $routes[] = [
              'doc_id' => $id,
              'route' => $route,
              'handler' => $handler ? $handler : null
            ];
          }
          DocRoute::create_all($routes);
        }
      }
      return $success;
    }
  }

?>
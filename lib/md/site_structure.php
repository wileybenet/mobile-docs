<?php // Doc heirarchical service

  class SiteStructure {
    private static $flat_pages;
    private static $site_map;
    private static $page_title;

    // retreive docs from the db and construct heirarchical tree
    public static function initialize() {
      $res = Database::$mysql->query("SELECT doc.id, title, name, parent_doc_id AS 'parent_id', sort_order FROM doc INNER JOIN nav ON doc.id = nav.doc_id ORDER BY sort_order;");

      self::$site_map = new NodeTree(['id' => 0, 'name' => '']);
      self::$flat_pages = [];

      $flat_page_name = self::get_flat_page_name();
      while ($row = $res->fetch_assoc()) {
        if ($row['name'] == $flat_page_name) {
          $row['selected'] = TRUE;
        }
        self::$flat_pages[$row['name']] = $row;
      }
      self::$site_map->construct(self::$flat_pages);
    }

    public static function get_request_uri() {
      if (SUBDIR != '') {
        return str_replace(SUBDIR, '', $_SERVER['REQUEST_URI']);
      }
      return $_SERVER['REQUEST_URI'];
    }

    // nav element getter
    public static function main_nav() {
      $nav = [];
      $pages = self::$site_map->pull_level(1, ['title', 'name', 'selected']);
      foreach ($pages as $item) {
        $nav[] = $item;
      }
      return $nav;
    }

    // route generator for all pre-defined pages
    public static function get_routes() {

      return Filter::array_map(function($route) {
        return [$route['selector'] => $route['handler']];
      }, DocRoute::read_all());
    }

    // admin nav element getter
    public static function admin_nav() {
      $nav = [
        ['name' => 'menu-editor', 'title' => 'Menus'],
        ['name' => 'doc-editor', 'title' => 'Pages'],
        ['name' => 'post-editor', 'title' => 'Posts'],
        ['name' => 'solution-editor', 'title' => 'Solutions'],
        ['name' => 'asset-manager', 'title' => 'Assets']
      ];
      $flat_page_name = self::get_flat_page_name();
      foreach ($nav as $idx => $el) {
        if ($el['name'] === $flat_page_name) {
          $nav[$idx]['selected'] = TRUE;
        }
      }
      return $nav;
    }

    // page title getter
    public static function page_title() {
      if (isset(self::$page_title)) {
        return self::$page_title;
      }
      $flat_page_name = self::get_flat_page_name();
      $title = isset($flat_pages[$flat_page_name]) ? $flat_pages[$flat_page_name]['title'] : FALSE;
      if ($title === FALSE) {
        $route = '/' . Router::$current_route_template;
        $doc = Doc::by_route($route);
        if (isset($doc['title'])) {
          $title = $doc['title'];
        }
      }
      return $title;
    }

    // override default page title
    public static function set_page_title($title) {
      self::$page_title = $title;
    }

    // flat page name (routing id might not be set yet)
    private static function get_flat_page_name() {
      if (isset(Router::$current_route_template)) {
        return Router::$current_route_template;
      }
      $request = Filter::trim_trailing_slash(self::get_request_uri());
      $request_full = explode('?', $request);
      $request_parts = explode('/', substr($request_full[0], 1));
      return $request_parts[0] ? end($request_parts) : 'home';
    }

  }

?>
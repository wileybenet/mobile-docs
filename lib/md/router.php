<?php // Routing service

  $_PUT;
  $_PST;

  class Router {
    protected static $current_request;
    public static $routes;
    public static $current_route_template;

    // aggregate route configurations
    // parse and set Post/Put data if provided
    public static function initialize() {
      global $HTTP_RAW_POST_DATA;
      global $_PUT;
      global $_PST;

      self::$routes = SiteStructure::get_routes();

      require_once(CONFIG . 'routes.php');

      self::admin_routes();

      // Format PUT/POST data as an associative array
      if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $_PUT = json_decode(file_get_contents('php://input'), TRUE);
      } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_PST = json_decode($HTTP_RAW_POST_DATA, TRUE);
        foreach ($_POST as $key => $value) {
          $_PST[$key] = $value;
        }
      }
    }

    // matches method and request with first route template
    public static function resolve($method, $original_req) {
      global $_PST;
      global $_PUT;

      //echo '<pre>'; debug_print_backtrace(); print_r($original_req); echo '</pre>'; exit;
      $uri = urldecode(Filter::trim_trailing_slash(explode('?', $original_req)[0]));
      self::$current_request = $method . '/' . implode('/', explode('/', substr($uri, 1)));
      $request_segments = explode('/', self::$current_request);

      $args = $_GET;
      switch ($method) {
        case 'POST':
          $args += $_PST;
          break;
        case 'PUT':
          $args += $_PUT;
          break;
      }
      $args['URI'] = $uri;

      return self::find_route($request_segments, $args);
    }

    public static function match_route($req, $method = 'GET') {

      $uri = Filter::trim_trailing_slash(explode('?', $req)[0]);
      $uri = $method . '/' . implode('/', explode('/', substr($uri, 1)));
      $request_segments = explode('/', $uri);

      $route = self::find_route($request_segments);
      return isset($route['route_template']) ? ('/' . $route['route_template']) : FALSE;
    }

    private static function find_route($request_segments, $arguments = []) {

      foreach (array_reverse(self::$routes) as $rt => $ctrl) {
        $args = $arguments;
        $route_segments = explode('/', $rt);
        $count = 0;
        // concurrently loop through route and uri segments
        if (count($request_segments) === count($route_segments)) {
          foreach ($route_segments as $rt_seg) {
            $length = count($route_segments);
            if (isset($request_segments[$count]) && ($rt_seg === $request_segments[$count] || (isset($rt_seg[0]) && $rt_seg[0] === ':'))) {
              // assign templated key=>value pairs
              if (isset($rt_seg[0]) && $rt_seg[0] === ':') {
                $key = substr($rt_seg, 1);
                $value = $request_segments[$count];
                $args[$key] = $value;
              }
              $count += 1;
              // resolve with template and key=>values if template successfully processes uri
              if ($count === $length) {
                $parts = explode('#', $ctrl);
                array_shift($route_segments);
                $route_id = implode('/', $route_segments);
                Router::$current_route_template = $route_id;
                return ['ctrl' => $parts[0] . 'Ctrl', 'method' => $parts[1], 'args' => $args, 'route_template' => $route_id];
              }
            // exit route if template match failed
            } else {
              break;
            }
          }
        }
      }
      return FALSE;
    }

    public static function is_api_request() {
      return strpos($_SERVER['REQUEST_URI'], SUBDIR . '/api/') === 0;
    }

    // route list getter
    public static function get_routes() {
      return self::$routes;
    }

    // formatted request getter
    public static function get_request() {
      return self::$current_request;
    }

    // namespace definition for explicit routes
    public static function ns_route($namespace, $routes) {
      foreach ($routes as $route => $resolution) {
        $route_parts = explode('@', $route);
        $methods = explode('|', $route_parts[0]);
        foreach ($methods as $method) {
          self::$routes["$method$namespace{$route_parts[1]}"] = $resolution;
        }
      }
    }

    // resource definition for predefined routes
    public static function resource($namespace, $model) {
      $class = strtoupper($model[0]) . substr($model, 1);
      self::ns_route($namespace, [
        "GET@$model" => "$class#read_all",
        "GET@$model/:id" => "$class#read_one",
        "PUT@$model/:id" => "$class#update_one",
        "POST@$model" => "$class#create_one"
      ]);
    }

    private static function admin_routes() {
      self::ns_route('/md/', [
        'GET@admin' => 'Admin#admin',
        'POST@login' => 'Admin#login',
        'GET@logout' => 'Admin#logout',
        'GET@adhits' => 'Admin#adhits',
        'GET@bannerhits' => 'Admin#bannerhits',
        'GET@evaluators' => 'Admin#evaluators',
        'GET@quotes' => 'Admin#quotes',
        'GET@routes' => 'Admin#routes',
        'GET@doc-editor' => 'Admin#page_editor',
        'GET@post-editor' => 'Admin#post_editor',
        'GET@menu-editor' => 'Admin#menu_editor',
        'GET@solution-editor' => 'Admin#solution_editor',
        'GET@asset-manager' => 'Admin#asset_manager',
        'GET@interface/html' => 'Admin#html_interface'
      ]);
    }

  }

?>
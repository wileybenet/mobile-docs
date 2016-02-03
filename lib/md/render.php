<?php // HTML, JSON rendering service

  class Render {
    private static $scripts = [];

    // initialize
    public static function initialize() {
    }

    // rendering method for requests
    public static function html($content, $data = [], $content_type = NULL) {
      $include = !isset($content_type);
      $content_type = isset($content_type) ? $content_type : 'text/html';
      header("Content-Type: $content_type");

      if (file_exists($content)) {
        $file = fopen($content, 'r') or die('Unable to open file!');
        $size = filesize($content);
        $content = fread($file, $size);
        fclose($file);
      }
      $template = new Template($content, TRUE);
      $scraped_template = $template->scrape($data);
      if ($include) {
        self::include_header();
      }
      echo $scraped_template->render($data);
      if ($include) {
        self::include_footer();
      }
    }

    // rendering method for requests
    public static function php($path) {
      self::include_header();
      require_once($path);
      self::include_footer();
    }

    // rendering method for json requests
    public static function json($response) {
      header('Content-Type: application/json');
      echo json_encode($response);
    }

    // rendering method for text requests
    public static function text($response) {
      header('Content-Type: text/plain');
      echo $response;
    }

    // rendering method for file requests
    public static function file($response) {
      header('Content-Type: ' . $response['type']);
      header('Content-Length: ' . $response['size']);
      fpassthru($response['contents']);
    }

    // rendering method for not-found requests
    public static function error_404() {
      require_once(HTML . '404.php');
      exit();
    }

    // rendering method for not-found api requests
    public static function error_json_404($req = '', $info = []) {
      header('Content-Type: application/json');
      echo json_encode([$req => 'error_not_found', 'valid_routes' => $info]);
      exit();
    }

    // helper method for including header
    private static function include_header() {
      $template = new Template(HTML . 'header.php');
      echo $template->render([
        'page_title' => SiteStructure::page_title(),
        'admin_nav' => SiteStructure::admin_nav(),
        'main_nav' => SiteStructure::main_nav(),
        'admin' => isset(Session::$admin_user['id']),
        'root' => SUBDIR
      ]);
      if (isset($_REQUEST['adid'])) {
        $model = [
          'ip' => $_SERVER['REMOTE_ADDR'],
          'uri' => $_SERVER['REQUEST_URI']
        ];
        Adhit::create($model);
      }
    }

    // helper method for including footer
    private static function include_footer() {
      require_once(HTML . 'footer.php');
    }

    public static function get_script_names() {
      if (count(self::$scripts) > 0) {
        return implode(", ", array_map(function($key) {
          $parts = explode('/', $key);
          return "'" . (strpos($key, 'lib') === 0 ? array_pop($parts) : $key) . "'";
        }, array_keys(self::$scripts)));
      } else {
        return '';
      }
    }

    public static function get_scripts() {
      if (count(self::$scripts) > 0) {
        $defaults = ['lib/angular', 'md/filters', 'md/factories', 'md/services', 'md/directives'];
        $scripts = array_map('self::write_script_tag', $defaults);
        foreach (array_keys(self::$scripts) as $script) {
          $scripts[] = self::write_script_tag($script);
        }
        return implode("\n    ", $scripts);
      } else {
        return '';
      }
    }

    public static function write_script_tag($script) {
      return "<script type=\"text/javascript\" src=\"/assets/js/$script.js\"></script>";
    }

    public static function include_script($name) {
      $js_file_path = SCRIPTS . "$name.js";
      $js_file = fopen($js_file_path, 'r') or die ('Unable to open file!');
      $js_file_contents = fread($js_file, filesize($js_file_path));
      $first_line = explode("\n", $js_file_contents)[0];
      preg_replace_callback("/((,|\s|\[)'([^']+)')/", function($matches) {
        $name = $matches[3];
        if (substr($name, 0, 2) === 'ng') {
          $name = 'lib/' . $name;
        }
        self::include_script($name);
        self::add_script($name);
      }, $first_line);
      fclose($js_file);

      self::add_script($name);
    }

    public static function add_script($script) {
      self::$scripts["$script"] = TRUE;
    }

  }

?>
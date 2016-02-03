<?php // Tempating class

  require_once(LIB . 'mustache/Autoloader.php');
  Mustache_Autoloader::register();

  class Template {
    private $html_str;
    private $injection_data;

    // instaniate tempate based on string or file path
    public function __construct($file_name, $from_str = FALSE) {
      if ($from_str) {
        $str = $file_name;
      } else {
        $file_api = fopen($file_name, "r") or die("Unable to open '$file_name'");
        $str = fread($file_api, filesize($file_name));
        fclose($file_api);
      }
      $this->html_str = $str;
      $this->injection_data = [];
    }

    // scrape tempate string for @{var} and evaluate
    public function scrape($context = []) {
      // scrape for injected data mapping
      $this->html_str = preg_replace_callback('/{{#@([^:]+):([^}:]*):?([^}]*)?}}/', function($matches) use (&$context) {
        $key = trim($matches[1]);
        $parts = explode('-', $key, 2);
        $class = $parts[0];
        $member = trim($matches[2]);
        $params = isset($matches[3]) ? explode(':', trim($matches[3])) : [];

        array_unshift($params, $context);

        $this->injection_data[$key] = forward_static_call_array([$class, $member], $params);

        return '{{#' . $key . '}}';
      }, $this->html_str);
      $this->html_str = preg_replace_callback('/{{@([^:]+):([^}:]*):?([^}]*)?}}/', function($matches) use (&$context) {
        $class = trim($matches[1]);
        $member = trim($matches[2]);
        $params = isset($matches[3]) ? explode(':', trim($matches[3])) : [];
        $key = str_replace('.', '_', $class . '_' . $member . '_' . implode('_', $params));

        array_unshift($params, $context);

        $this->injection_data[$key] = forward_static_call_array([$class, $member], $params);

        return '{{' . $key . '}}';
      }, $this->html_str);
      return $this;
    }

    // inject template with explict or scraped data
    // return rendered string
    public function render($data = []) {
      $m = new Mustache_Engine(['pragmas' => ['FILTERS']]);
      self::initialize_filters($m);
      foreach ($this->injection_data as $key => $val) {
        $data[$key] = $val;
      }
      return $m->render($this->html_str, $data);
    }

    private static function initialize_filters($m) {
      // inject commas
      // example usage:
      // {{var|,}}
      // var = 12345, output '12,345'
      $m->addHelper(',', function($value) { return self::commas($value); });
      // inject commas and prefix with $
      // example usage:
      // {{var|$}}
      // var = 12345, output '$12,345'
      $m->addHelper('$', function($value) { return '$' . self::commas($value); });
      // simple filters for justifying text
      // define width for left and right filters
      for ($arg = 0; $arg < 10; ++$arg) {
        $m->addHelper("$arg", function($value) use ($arg) {
          $result = is_array($value) ? $value : [ 'value' => $value, 'arg' => 0];
          $result['arg'] *= 10;
          $result['arg'] += $arg;
          return $result;
        });
      }
      // example usage:
      // {{var|6|right}}
      // var = 'abc', output '   abc' (6 wide)
      $m->addHelper('left', function($value) {
        if (!is_array($value)) {
          return $value;
        }
        $s = $value['value'];
        $arg = $value['arg'];
        $s = sprintf('%-' . $arg . 's', $s);
        return $s;
      });
      // example usage:
      // {{var|1|1|left}}
      // var = 'abc', output 'abc        ' (11 wide)
      $m->addHelper('right', function($value) {
        if (!is_array($value)) {
          return $value;
        }
        $s = $value['value'];
        $arg = $value['arg'];
        $s = sprintf('%' . $arg . 's', $s);
        return $s;
      });

      // example usage:
      // {{array|join}}
      // array = [1,2,3], output '1, 2, 3'
      $m->addHelper('join', function($value) {
        if (!is_array($value)) {
          return $value;
        }
        return implode(', ', $value);
      });

      // example usage:
      // {{var|eat1}}
      // var = 1, output ''
      // var = 2, output '2'
      $m->addHelper('eat1', function($value) {
        return $value == 1 ? '' : $value;
      });

      // example usage:
      // {{var|plural}}
      // var = 1, output ''
      // var = 2, output 's'
      $m->addHelper('plural', function($value) {
        return $value == 1 ? '' : 's';
      });
    }

    private static function commas($s) {
        $i = strlen($s) - 3;
        while ($i > 0) {
          $s = substr($s, 0, $i) . ',' . substr($s, $i);
          $i -= 3;
        }
        return $s;
    }

    public static function render_doc_by_name($name, $context = []) {
      $template = new Template(Doc::by_name($name)['content'], TRUE);
      return $template->scrape($context)->render($context);
    }
  }

?>
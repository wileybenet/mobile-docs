<?php

  class Ctrl {

    // bootstrap function for processing routing information (Ctrl#method)
    public static function process($parsed_request) {
      if (!$parsed_request) {
        return ['error' => 'does not exist'];
      }
      // Instantiate the controller
      $ctrl = new $parsed_request['ctrl']();
      // Call the api-defined method on the controller object
      // passing any uri args set by "/:{var}/" in the api route template
      return $ctrl->$parsed_request['method']($parsed_request['args']);
    }

    public function page($params = []) {
      $parts = explode('/', $params['URI']);
      $page = strlen(end($parts)) > 1 ? end($parts) : 'home';
      $doc = Doc::by_name($page);
      Render::html($doc['content'], [], $doc['type']);
    }

    public function doc($params = []) {
      $route = '/' . Router::$current_route_template;
      $doc = Doc::by_route($route);
      Render::html($doc['content'], $params, $doc['type']);
    }

    public function support() {
      if (Session::$user) {
        Render::html(Doc::by_name('support')['content'], ['email' => Session::$user['email']]);
      } else {
        Render::php(HTML . 'login.php');
      }
    }

    public function read_all($params = []) {
      $class = Filter::controller_model(get_called_class());

      Render::json($class::read());
    }

    public function read_one($params = []) {
      $class = Filter::controller_model(get_called_class());

      Render::json($class::read(['*'], $params['id']));
    }

    public function update_one($params = []) {
      Session::permit_admin();
      $class = Filter::controller_model(get_called_class());

      $class::update(Record::allow($params, ['name', 'title']), $params['id']);

      Render::json($class::read(['*'], $params['id']));
    }

    public function create_one($params = []) {
      Session::permit_admin();
      $class = Filter::controller_model(get_called_class());

      $item = $class::create(Record::allow($params, ['name', 'title']));

      Render::json($class::read(['*'], $item['id']));
    }
    
  }

?>
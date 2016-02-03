<?php

  class SolutionCtrl extends Ctrl {

    public function read_all($params = []) {
      Render::json(Solution::read_formatted());
    }

    public function read_one($params = []) {
      Render::json(Solution::read_formatted($params['id']));
    }

    public function update_one($params = []) {
      global $_PUT;
      Solution::update($_PUT, $params['id']);
      Render::json(Solution::read_formatted($params['id']));
    }

    public function create_one($params = []) {
      global $_PST;
      Render::json(Solution::create($_PST));
    }
    
    public function version($params = []) {
      Render::json(SolutionContent::read(['*'], FALSE, ["solution_id = ?", $params['solution_id']], 'updated DESC'));
    }

    public function asset($params = []) {
      $assets = [];
      $dir = UPLOADS . 'solution/' . $params['solution_name'];
      if (is_dir($dir)) {
        foreach(scandir($dir) as $path) {
          if ($path[0] != '.') {
            $assets[] = $path;
          }
        }
      }
      Render::json($assets);
    }

    public function upload($params = []) {
      $solution_name = $params['solution_name'];
      $dir = UPLOADS . "solution/$solution_name";
      if (!file_exists($dir)) {
        mkdir($dir, 0777, TRUE);
      }
      foreach ($_FILES['file']['tmp_name'] as $idx => $tmp_name) {
        $name = $_FILES['file']['name'][$idx];
        move_uploaded_file($tmp_name, $dir . "/$name");
      }
      header('Location: '. $params['loc']);
    }
  }

?>
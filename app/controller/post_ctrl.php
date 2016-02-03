<?php

  class PostCtrl extends Ctrl {

    public function read_all($params = []) {
      Render::json(Post::read_formatted());
    }

    public function read_one($params = []) {
      Render::json(Post::read_formatted($params['id']));
    }

    public function update_one($params = []) {
      global $_PUT;
      Post::update($_PUT, $params['id']);
      Render::json(Post::read_formatted($params['id']));
    }

    public function create_one($params = []) {
      global $_PST;
      Render::json(Post::create($_PST));
    }
    
    public function version($params = []) {
      Render::json(PostContent::read(['*'], FALSE, ['post_id = ?', $params['post_id']], 'updated DESC'));
    }

    public function asset($params = []) {
      $assets = [];
      $dir = UPLOADS . 'post/' . $params['post_name'];
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
      $post_name = $params['post_name'];
      $dir = UPLOADS . "post/$post_name";
      if (!file_exists($dir)) {
        mkdir($dir, 0777, TRUE);
      }
      foreach ($_FILES['file']['tmp_name'] as $idx => $tmp_name) {
        $name = $_FILES['file']['name'][$idx];
        move_uploaded_file($tmp_name, $dir . '/' . $name);
      }
      header('Location: '. $_POST['loc']);
    }
  }

?>
<?php

  class DocCtrl extends Ctrl {

    public function read_all($params = []) {
      Render::json(Doc::read_content());
    }

    public function read_one($params = []) {
      $id = isset($params['id']) ? $params['id'] : TRUE;
      Render::json(Doc::read_content($id));
    }

    public function update_one($params = []) {
      Session::permit_admin();

      Doc::update($params, $params['id']);

      Nav::update_location($params);

      Render::json(Doc::read_content($params['id']));
    }

    public function create_one($params = []) {
      Session::permit_admin();

      $doc = Doc::create_nav($params);

      DocContent::create(['doc_id' => $doc['id'], 'content' => '']);

      Render::json(Doc::read_content($doc['id']));
    }

    public function update_nav($params = []) {
      Session::permit_admin();

      foreach ($params['parent'] as $doc_id => $parent_id) {
        $nav = Nav::read_by_doc($doc_id);
        Nav::update(['parent_doc_id' => $parent_id], $nav['id']);
      }
      foreach($params['order'] as $doc_id => $sort_index) {
        $nav = Nav::read_by_doc($doc_id);
        Nav::update(['sort_order' => $sort_index], $nav['id']);
      }

      Render::json(['success' => TRUE]);
    }

    public function asset($params = []) {
      $assets = [];
      foreach(scandir(IMAGES) as $path) {
        if ($path[0] != '.') {
          $assets[] = $path;
        }
      }
      Render::json($assets);
    }

    public function version($params = []) {
      $contents = DocContent::read(['*'], FALSE, ['doc_id = ?', $params['doc_id']], 'updated DESC');
      Render::json(array_slice($contents, 0, 10));
    }

    public function upload($params = []) {
      Session::permit_admin();
      
      foreach ($_FILES['file']['tmp_name'] as $idx => $tmp_name) {
        $name = $_FILES['file']['name'][$idx];
        move_uploaded_file($tmp_name, IMAGES . $name);
      }
      header('Location: '. $_POST['loc']);
    }

    public function template($params = []) {
      Render::text(Doc::by_name($params['name'])['content']);
    }

  }

?>
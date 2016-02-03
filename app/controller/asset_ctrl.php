<?php

  class AssetCtrl extends Ctrl {

    public function upload($params = []) {
      $post_name = $params['collection_name'];
      $dir = UPLOADS . "general/$post_name";
      if (!file_exists($dir)) {
        mkdir($dir, 0777, TRUE);
      }
      foreach ($_FILES['file']['tmp_name'] as $idx => $tmp_name) {
        $name = $_FILES['file']['name'][$idx];
        move_uploaded_file($tmp_name, $dir . "/$name");
      }
      header('Location: '. $_POST['loc']);
    }

    public function asset($params = []) {
      $assets = [];
      $dir = UPLOADS . 'general/' . $params['collection_name'];
      if (is_dir($dir)) {
        foreach(scandir($dir) as $path) {
          if ($path[0] != '.') {
            $assets[] = $path;
          }
        }
      }
      Render::json($assets);
    }

  }

?>
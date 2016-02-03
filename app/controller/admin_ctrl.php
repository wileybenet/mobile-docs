<?php

  class AdminCtrl extends Ctrl {

    public function admin($params = []) {
      if (isset(Session::$admin_user['id'])) {
        header('Location: ' . SUBDIR . '/md/doc-editor');
      } else {
        require_once(ADMIN . 'login.php');
      }
    }

    public function login($params = []) {
      if ($error = Session::authorize_admin($params['username'], $params['password'])) {
        Session::$error = $error;
        header('Location: ' . SUBDIR . '/md/admin');
      } else {
        header('Location: ' . SUBDIR . '/md/doc-editor');
      }
    }

    public function logout($params = []) {
      Session::terminate();
      header('Location: ' . SUBDIR . '/');
    }

    public function page_editor($params = []) {
      Session::permit_admin();
      SiteStructure::set_page_title('Manage Pages');
      Render::php(ADMIN . 'doc-editor.php');
    }

    public function post_editor($params = []) {
      Session::permit_admin();
      SiteStructure::set_page_title('Manage Posts');
      Render::php(ADMIN . 'post-editor.php');
    }

    public function solution_editor($params = []) {
      Session::permit_admin();
      SiteStructure::set_page_title('Manage Solutions');
      Render::php(ADMIN . 'solution-editor.php');
    }

    public function menu_editor($params = []) {
      Session::permit_admin();
      SiteStructure::set_page_title('Manage Menus');
      Render::php(ADMIN . 'menu-editor.php');
    }

    public function asset_manager($params = []) {
      Session::permit_admin();
      SiteStructure::set_page_title('Manage Assets');
      Render::php(ADMIN . 'asset-manager.php');
    }

    public function html_interface($params = []) {
      Session::permit_admin();

      $data = ['items' => Util::parse_html_interface()];
      $template = new Template(MD . 'templates/html_interface.html');

      Render::text($template->render($data));
    }

    public function adhits($params = []) {
      Session::permit_admin();
      SiteStructure::set_page_title('Ad Hits');
      Render::php(ADMIN . 'adhits.php');
    }

    public function bannerhits($params = []) {
      Session::permit_admin();
      SiteStructure::set_page_title('Banner Hits');
      Render::php(ADMIN . 'bannerhits.php');
    }

    public function console($params = []) {
      Session::permit_admin();
      Render::php(ADMIN . 'console.php');
    }

    public function debug($params = []) {
      Session::permit_admin();

      require_once(REF . "doc2/toc.php");
      print_r($dev_net[0]);
    }

    public function evaluators($params = []) {
      Session::permit_admin();
      SiteStructure::set_page_title('Evaluators');
      Render::php(ADMIN . 'evaluators.php');
    }

    public function quotes($params = []) {
      Session::permit_admin();
      SiteStructure::set_page_title('Quotes');
      Render::php(ADMIN . 'quotes.php');
    }

    public function routes($params = []) {
      Session::permit_admin();
      SiteStructure::set_page_title('Routes');
      Render::php(ADMIN . 'routes.php');
    }

  }

?>
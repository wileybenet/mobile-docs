<?php

  class SessionCtrl extends Ctrl {

    public function login($params = []) {
      Session::$error = FALSE;
      $email = isset($_POST['email']) ? $_POST['email'] : '';
      $password = isset($_POST['password']) ? $_POST['password'] : '';
      $user = AdminUser::read_one($params['email']);
      if (!isset($user['username'])) {
        header('Location: https://www.winwrap.com/web/basic/support/login.asp?A=' . urlencode($email) . '&P=' . urlencode($password));
      }
      else {
        if (isset($_POST['email'])) {
          Session::$error = Session::authorize_admin($email, $password);
        }

        if (Session::$error) {
          Render::php(HTML . 'login.php');
        } else {
          header('Location: ' . SUBDIR . '/md/doc-editor');
        }
      }
    }

    public function logout() {
      Session::terminate();
      header('Location: ' . SUBDIR . '/support');
    }
  }

?>
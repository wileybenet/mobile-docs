<?php // Session service

  class Session {
    public static $user;
    public static $admin_user;
    public static $error;

    // get current user based on id in PHPSESSID cookie
    public static function start() {
      session_start();
      if (isset($_SESSION['user_id'])) {
        self::get_user($_SESSION['user_id']);
      } elseif (isset($_SESSION['admin_user_id'])) {
        self::get_admin_user($_SESSION['admin_user_id']);
      }
    }

    // authenticate an email, password combination
    // return errors
    public static function authorize($email, $password) {
      if ($user = User::find_one($email, $password)) {
        self::$user = $user;
        $_SESSION['user_id'] = $user['id'];
      } else {
        return 'Incorrect email or password.';
      }
    }

    public static function authorize_admin($username, $password) {
      if ($user = AdminUser::find_one($username, $password)) {
        self::$admin_user = $user;
        $_SESSION['admin_user_id'] = $user['id'];
        print_r($_SESSION);
        return FALSE;
      } else {
        return 'Incorrect email or password.';
      }
    }

    // blanket filter for auth thresholds
    // exits php execution if not permitted
    public static function permit_admin() {
      if (!self::is_admin()) {
        Render::error_404();
      }
    }

    // terminate current session
    public static function terminate() {
      session_destroy();
      session_write_close();
    }

    // get user by id
    private static function get_user($id) {
      self::$user = User::read(['*'], $id);
    }

    // get admin user by id
    private static function get_admin_user($id) {
      self::$admin_user = AdminUser::read(['*'], $id);
    }

    // true if admin
    public static function is_admin() {
      return isset(self::$admin_user['id']);
    }

  }

?>
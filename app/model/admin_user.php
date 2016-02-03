<?php

  class AdminUser extends Record {

    public static function read_one($username) {
      return parent::read(['*'], TRUE, ['username = ?', $username]);
    }

    public static function find_one($username, $password) {
      $c_password = crypt($password, SALT);
      $model = parent::read(['*'], TRUE, ['username = ? AND password = ?', $username, $c_password]);
      return isset($model['error']) ? FALSE : $model;
    }

    public static function create_one($model) {
      $model = Record::allow($model, ['username', 'password']);
      $model['password'] = crypt($model['password'], SALT);
      return parent::create($model);
    }

    public static function read_admin($params) {
      if (!Session::is_admin()) {
        return [];
      }
      return Session::$admin_user;
    }
  }

?>
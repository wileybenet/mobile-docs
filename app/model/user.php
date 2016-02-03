<?php

  class User extends Record {
    public static $table = 'user';

    public static function find_one($email, $password) {
      $c_password = crypt($password, SALT);
      $model = parent::read(['*'], TRUE, ['email = ? AND password = ?', $email, $c_password]);
      return isset($model['error']) ? FALSE : $model;
    }

    public static function update_one($model, $id) {
      $model = Record::allow($model, ['email', 'password', 'auth']);
      if (isset($model['password'])) {
        $model['password'] = crypt($model['password'], SALT);
      }
      return parent::update($model, $id);
    }

    public static function create_one($model) {
      $model = Record::allow($model, ['email', 'password', 'auth']);
      $model['password'] = crypt($model['password'], SALT);
      return parent::create($model);
    }
  }

?>
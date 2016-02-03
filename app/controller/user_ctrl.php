<?php

  class UserCtrl extends Ctrl {

    public function read_all($params = []) {
      Render::json(User::read(['email']));
    }

    public function read_one($params) {
      Render::json(User::read(['email'], $params['id']));
    }

    public function update_one($params) {
      Session::permit_admin();
      
      global $_PUT;
      Render::json(User::update($_PUT, $params['id']));
    }

    public function create_one($params) {
      Session::permit_admin();

      global $_PST;
      Render::json(User::create($_PST));
    }
  }

?>
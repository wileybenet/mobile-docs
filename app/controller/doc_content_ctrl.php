<?php

  class DocContentCtrl extends Ctrl {

    public function create_one($params = []) {
      Session::permit_admin();
      
      return DocContent::create($params);
    }
  }

?>
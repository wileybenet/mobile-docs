<?php

  class Nav extends Record {

    protected static $props = [];

    public static function update_location($doc) {
      $nav = self::read_by_doc($doc['id']);
      $changes = [];
      if (array_key_exists('parent_id', $doc)) {
        $changes['parent_doc_id'] = $doc['parent_id'];
      }
      if (array_key_exists('order', $doc)) {
        $changes['sort_order'] = $doc['order'];
      }
      parent::update($changes, $nav['id']);
    }

    public static function read_by_doc($doc_id) {
      return parent::read(['id'], FALSE, ['doc_id = ?', $doc_id])[0];
    }

  }

?>
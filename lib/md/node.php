<?php // tree node {data, id, children}
  
  class Node {
    public $data;
    public $children;

    public function __construct($node) {
      $this->data = $node;
      $this->id = $node['id'];
      $this->children = [];
    }
  }

?>
<?php // tree root

  class NodeTree {
    public $root;

    // initialize the tree root
    public function __construct($root) {
      $this->root = new Node($root);
    }

    // construct the tree by finding parent (branch) of each node
    public function construct($nodes) {
      usort($nodes, Filter::sort_by('parent_id'));

      foreach ($nodes as $name => $node) {
        if (isset($node['parent_id'])) {
          $this->step($this->root, $node);
        }
      }
      usort($this->root->children, function($a, $b) {
        return $a->data['sort_order'] - $b->data['sort_order'];
      });
    }

    // extract all leaves at a given height in the tree
    public function pull_level($height, $keys) {
      $items = [];
      foreach ($this->get_level($this->root, 1, $height) as $node) {
        $item = [];
        foreach ($keys as $key) {
          $item[$key] = isset($node->data[$key]) ? $node->data[$key] : FALSE;
        }
        $items[] = $item;
      }
      return $items;
    }

    // branch walking function for describing the entire tree
    public function traverse() {
      return $this->roll_up([], $this->root);
    }

    // recursive function for stringifying leaf structure
    private function roll_up($base, $parent) {
      $name = $parent->data['name'];
      $base[] = $name;
      $routes = [$base];
      foreach ($parent->children as $child) {
        $routes += $this->roll_up($base, $child);
      }
      return $routes;
    }

    // recursive function used for searching by id
    private function step($current, $item) {
      if ($current->id == $item['parent_id']) {
        $current->children[] = new Node($item);
      } else {
        foreach($current->children as $node) {
          $this->step($node, $item);
        }
      }
    }

    // recursive function used for retreiving all leaves off a given branch
    private function get_level($node, $current, $target) {
      $level_arr = [];
      if ($current == $target) {
        $level_arr = $node->children;
      } else {
        foreach($node->children as $child) {
          $level_arr += $this->get_level($child, $current + 1, $target);
        }
      }
      return $level_arr;
    }

  }

?>
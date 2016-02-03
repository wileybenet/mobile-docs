<?php

  class Solution extends Record {

    protected static $props = [
      'content' => "(SELECT content FROM solution_content WHERE solution_id = solution.id ORDER BY updated DESC LIMIT 1) AS content"
    ];

    public static function read_formatted($id = FALSE) {
      return self::read(['*', self::$props['content']], $id, FALSE, 'date DESC');
    }

    public static function read_by_name($name) {
      return self::read(['*', self::$props['content']], TRUE, ["name = ?", $name]);
    }

    public static function read_by_params_and_render($params) {
      $solution = self::read_by_name($params['solution_name']);
      SiteStructure::set_page_title($solution['title']);
      $template = new Template($solution['content'], TRUE);
      $solution['content'] = $template->scrape($solution)->render();
      return $solution;
    }

    public static function update($model, $id, $update_lit = '') {
      $success = parent::update(self::allow($model, ['title', 'summary']), $id, $update_lit);
      if (isset($model['content'])) {
        $success = SolutionContent::create(['solution_id' => $id, 'content' => $model['content']]);
      }
      return $success;
    }
    
  }

?>
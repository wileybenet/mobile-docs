<?php

  class Post extends Record {

    protected static $props = [
      'content' => "(SELECT content FROM post_content WHERE post_id = post.id ORDER BY updated DESC LIMIT 1) AS content",
      'formatted_date' => "DATE_FORMAT(date, '%M %d, %Y') AS 'formatted_date'",
      'slash_date' => "DATE_FORMAT(date, '%Y/%m/%d') AS 'slash_date'"
    ];

    /* @HTMLI
    *  returns: array of posts { title, summary, formatted_date, slash_date }
    */
    public static function read_formatted($id = FALSE) {
      return self::read(['*', self::$props['formatted_date'], self::$props['slash_date'], self::$props['content']], $id, FALSE, 'date DESC');
    }

    public static function read_by_date($year, $month, $day) {
      return self::read(['*', self::$props['formatted_date'], self::$props['slash_date'], self::$props['content']], TRUE, ["date = '?-?-?'", intval($year), intval($month), intval($day)], 'date DESC');
    }

    public static function read_by_name($name) {
      return self::read(['*', self::$props['formatted_date'], self::$props['slash_date'], self::$props['content']], TRUE, ["name = ?", $name], 'date DESC');
    }

    public static function read_by_params_and_render($params) {
      if (isset($params['post_name'])) {
        $post = self::read_by_name($params['post_name']);
      } else {
        $post = self::read_by_date($params['year'], $params['month'], $params['day']);
      }
      SiteStructure::set_page_title($post['title']);
      $template = new Template($post['content'], TRUE);
      $post['content'] = $template->scrape($post)->render();
      return $post;
    }

    public static function create($key_value_pairs) {
      $key_value_pairs['date'] = gmdate('Y-m-d');
      return parent::create($key_value_pairs);
    }

    public static function update($model, $id, $update_lit = '') {
      $success = parent::update(self::allow($model, ['title', 'summary', 'date']), $id, $update_lit);
      if (isset($model['content'])) {
        $success = PostContent::create(['post_id' => $id, 'content' => $model['content']]);
      }
      return $success;
    }

  }

?>
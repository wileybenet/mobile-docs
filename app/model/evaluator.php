<?php

  class Evaluator extends Record {

    public static function read_by_key($key) {
      $evaluator = self::read(['*'], TRUE, ["`key` = ?", $key]);
      if (isset($evaluator['id'])) {
        $evaluator['downloads'] = EvaluatorDownload::read_by_evaluator_id($evaluator['id']);
      }
      return $evaluator;
    }

    public static function read_by_ip($ip) {
      return self::read(['*'], TRUE, ["ip = ?", $ip]);
    }

    public static function get_unique_key($model = []) {
      return self::unique_key("`key`", 12, $model['name'] . $model['company'] . $model['email']);
    }

  }

?>
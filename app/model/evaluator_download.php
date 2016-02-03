<?php

  class EvaluatorDownload extends Record {

    public static function read_by_evaluator_id($evaluator_id) {
      return self::read(['*'], FALSE, ["evaluator_id = ?", $evaluator_id], 'created DESC');
    }
  }

?>
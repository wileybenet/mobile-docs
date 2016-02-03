<?php

  class Test {

    public static function doit($params = []) {
      echo '<pre>'; debug_print_backtrace(); echo '</pre>'; exit;
    }
  }

?>

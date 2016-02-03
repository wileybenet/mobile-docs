<?php

  class Util2 {

    // $start: yyyy-mm-dd
    // $days: number of days into the future
    // $override: yyyy-mm-dd - if not null use this as the future date
    public static function future_date($start, $days, $override = null) {
      if (!is_null($override)) {
        return $override;
      }
      $d1 = new DateTime($start);
      $interval = new DateInterval('P' . $days . 'D');
      return $d1->add($interval)->format('Y-m-d');
    }

    // return true if expired
    public static function expired($start, $days, $override = null) {
      $now = gmdate('Y-m-d');
      return self::future_date($start, $days, $override) <= $now;
    }

    // return number of days left
    public static function days_left($start, $future_date) {
      $d1 = new DateTime($start);
      $d2 = new DateTime($future_date);
      $interval = $d2->diff($d1);
      return $interval->days;
    }

    public static function random_key($len) {
      $bytes = openssl_random_pseudo_bytes($len/2);
      return bin2hex($bytes);
    }

  }

?>

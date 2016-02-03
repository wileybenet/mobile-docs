<?php

  class Quote extends Record {

    public static function read_by_params($params) {
      if (strlen($params['part_or_key']) == 16) {
        $quote = self::read_key($params['part_or_key']);
        $quote['part_or_key2'] = 'key';
        if (!Session::is_admin() && array_key_exists('key', $quote)) {
          $model = [
            'quote_id' => $quote['id'],
            'ip' => $_SERVER['REMOTE_ADDR']
          ];
          QuoteView::create($model);
        }
      }
      else {
        $quote = self::read_part($params['part_or_key']);
        $quote['part_or_key2'] = $params['part_or_key'];
      }
      return $quote;
    }

    public static function read_part($part) {
      // create temporary quote for one part
      $item = [
        'quote_id' => 0,
        'quantity' => 1,
        'override' => null
      ];
      $item += Part::read_part($part);
      $item['annual_fee'] = $item['price'];
      $quote = [
        'id' => 0,
        'created' => null,
        'key' => null,
        'company' => null,
        'address' => null,
        'country' => null,
        'billing_name' => null,
        'billing_email' => null,
        'billing_phone' => null,
        'technical_name' => null,
        'technical_email' => null,
        'discount' => null,
        'discount_desc' => null,
        'first_fee' => $item['annual_fee'],
        'annual_fee' => $item['annual_fee'],
        'items' => [$item]
      ];
      return $quote;
    }

    public static function read_key($key) {
      $quote = self::read(['*'], TRUE, ["`key` = ?", $key]);
      $quote['items'] = QuoteItem::read_items($quote['id']);
      $annual_fee = 0;
      foreach ($quote['items'] as $item) {
        $annual_fee += $item['annual_fee'];
      }
      $quote['first_fee'] = $annual_fee;
      $quote['annual_fee'] = $annual_fee;
      if (!is_null($quote['discount'])) {
        $quote['first_fee'] -= $quote['discount'];
      }
      $quote['expiration'] = Util2::future_date(substr($quote['created'], 0, 10), 60);
      return $quote;
    }

    public static function get_unique_key($model = []) {
      return self::unique_key("`key`", 16, '');
    }

  }

?>
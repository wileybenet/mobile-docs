<?php

  class QuoteItem extends Record {

    public static function read_items($quote_id) {
      $items = QuoteItem::read(['*'], FALSE, ['quote_id = ?', $quote_id]);
      for ($i = 0; $i < count($items); ++$i) {
        $part = Part::read_part($items[$i]['part']);
        unset($part['id']);
        $items[$i] += $part;
        if (!is_null($items[$i]['override'])) {
          $items[$i]['price'] = $items[$i]['override'];
        }
        $items[$i]['annual_fee'] = $items[$i]['price']*$items[$i]['quantity'];
      }
      return $items;
    }

  }

?>
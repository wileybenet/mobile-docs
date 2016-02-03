<?php

  class QuoteView extends Record {

    public static function read_views($quote_id) {
      return QuoteView::read(['*'], FALSE, ['quote_id = ?', $quote_id]);
    }

  }

?>
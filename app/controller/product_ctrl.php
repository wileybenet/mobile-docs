<?php

  class ProductCtrl extends Ctrl {

    public function order_custom($params = []) {
      $parts = Part::get_parts();
      $order = Record::allow($params, array_keys($parts));
      $key = Quote::get_unique_key([]);
      $model['`key`'] = $key;
      $fields = ['company', 'address', 'country', 'billing_name', 'billing_email', 'billing_phone', 'technical_name', 'technical_email', 'discount', 'discount_desc'];
      $model += Record::allow($params, $fields);
      $quote = Quote::create($model);
      $total = 0;
      foreach ($order as $name => $value) {
        if ($value) {
          $model = [
            'quote_id' => $quote['id'],
            'part' => $name,
            'override' => null,
            'quantity' => $value
          ];
          QuoteItem::create($model);
        }
      }
      return Render::json(['key' => $key]);
    }

    public function order_request($params = []) {
      $quote = Quote::read_by_params($params);
      if (!isset($quote['id'])) {
        Render::error_404();
      }
      $order = $params;
      $order += $quote;

      $order['country'] = Util::get_countries()[$order['country']];
      $order['payment'] = Util::get_payments()[$order['payment']];
      if (!isset($order['po'])) {
        $order['po'] = '';
      }

      $body = Template::render_doc_by_name('order-email', $order);

      // send order request to "WinWrap Web Primary/web.primary@winwrap.com"
      $linkc = 
        'https://www.winwrap.com/web/basic/support/admin/new_company.asp' .
        '?license=16' .
        '&company=' . urlencode($order['company']) .
        //'&licensepayer=' . urlencode($order['third_party_payer']) .
        '&country=' . urlencode($order['country']) .
        '&billingaddress=' . urlencode(str_replace("\r\n", '|', $order['address'])) .
        '&billingname=' . urlencode($order['billing_name']) .
        '&billingaddr=' . urlencode($order['billing_email']) .
        '&billingphone=' . urlencode($order['billing_phone']) .
        '&name=' . urlencode($order['technical_name']) .
        '&addr=' . urlencode($order['technical_email']);
      $link =
        'https://www.winwrap.com/web/basic/support/admin/new_fees.asp' .
        '?licenseid=' .
        '&company=' . urlencode($order['company']) .
        '&feecount=0' .
        '&payment=' . urlencode($order['payment']) .
        '&po=' . urlencode($order['po']);
      if (!is_null($quote['discount'])) {
        $link .=
          '&WWXX00=' . urlencode($quote['discount']) .
          '&WWXX00_DESC=' . urlencode($quote['discount_desc']);
      }
      $problem = false;
      $parts = [];
      foreach ($quote['items'] as $item) {
        $link .=
          '&' . $item['part'] . '=' . $item['quantity'];
        $part = substr($item['part'], 0, 6);
        if (array_search($part, $parts)) {
          $problem = true;
        } else {
          $parts[] = $part;
        }
        if (isset($item['override'])) {
          $link .=
            '&' . $part . '-override=' . $item['override'];
        }
      }
      $bodyc =
        '1) Click on this link to create the new company:' . "\r\n" .
        $linkc . "\r\n\r\n";
      if ($problem) {
        $bodyc .=
          '*** the following link will not create the correct fees ***' . "\r\n";
      }
      $bodyc .=
        '2) Click on this link to create the fees and invoice:' . "\r\n" .
        $link . "\r\n\r\n" .
        $body;
      $args = [
        'IP: ' . $_SERVER['REMOTE_ADDR'] . "\r\n\r\n" .
        'toname' => 'WinWrap Web Primary',
        'toemail' => 'web.primary@winwrap.com',
        'fromname' => 'WinWrap Web Primary',
        'fromemail' => 'web.primary@winwrap.com',
        'subject' => 'WinWrap Basic Order Request',
        'body' => $bodyc
      ];
      if (GoogleMail::send($args) !== true) {
        header('Status: 500');
        return;
      }

      $status = '1/2: send to ' . $args['toemail'] . "\r\n";
      //echo '<pre>' . $body . '</pre>'; exit;
      // send order request to puchaser
      $args = [
        'toname' => $order['billing_name'],
        'toemail' => $order['billing_email'],
        'fromname' => 'WinWrap Support',
        'fromemail' => 'support@mail.winwrap.com',
        'subject' => 'WinWrap Basic Order Request',
        'body' => $body
      ];
      if (GoogleMail::send($args) !== true) {
        header('Status: 500');
        return;
      }

      $status .= '2/2: send to ' . $args['toemail'];
      Render::text($status);
    }

    public static function test() {
      $params = [
        'part_or_key' => 'WWXX42',
        //'part_or_key' => '50c974836f7a3b05', // 1 WWXX11, 2 WWAC12
        //'part_or_key' => '08cf51711b6f707e', // 1 WWXX11, 1 WWAC11, 2 WWAC11A
        'company' => 'Polar Engineering, Inc.',
        'address' => 'P.O. Box 7188' . "\r\n" . 'Nikiski, AK 99635',
        'country' => '1',
        'billing_name' => 'Tom Bennett',
        'billing_email' => 'tom.bennett@polarengineering.com',
        'billing_phone' => '907/776-5509',
        'technical_name' => 'Tom',
        'technical_email' => 'tom.bennett@winwrap.com',
        'payment' => '1',
        'license' => 'YES'
      ];
      $ctrl = new ProductCtrl();
      return $ctrl->order_request($params);
    }

  }

?>

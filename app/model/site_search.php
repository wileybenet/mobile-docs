<?php

  class SiteSearch {

    public static function search_all() {
      if (!isset($_REQUEST['query'])) {
        return [];
      }
      $text = $_REQUEST['query'];
      return self::search($text);
    }

    public static function search($text) {
      global $local;

      $text .= ' site:winwrap.com';

      // account: winwrap@hotmail.com
      $acctKey = '2itD5ZI02fv+ogdvYQj6b+aR2X15lOkohvjxLs+JszY';
      $cust_id = '658f1c2f-8942-4031-baca-8ffda74d59e3';

      // this implemenation is modelled after code from this page:
      // https://onedrive.live.com/view.aspx?resid=9C9479871FBFA822!112&app=Word&authkey=!ANNnJQREB0kDC04
      $rootUri = 'https://api.datamarket.azure.com/Bing/Search'; 

      // Encode the query and the single quotes that must surround it.
      $query = urlencode("'$text'");
      // Construct the full URI for the query.
      $requestUri = "$rootUri/Web?\$format=json&Query=$query";

      // Encode the credentials and create the stream context.
      $auth = base64_encode("$acctKey:$acctKey");
      $data = [
        'http' => [
          'request_fulluri' => true,
          // ignore_errors can help debug – remove for production. This option added in PHP 5.2.10
      //    'ignore_errors' => true,
          'header' => "Authorization: Basic $auth"]
      ];

      //echo '<pre>'; print_r($data); echo '</pre>'; exit;
      $context = stream_context_create($data);

      // Get the response from Bing.

      $response = file_get_contents($requestUri, 0, $context);
      //echo '<pre>'; print_r($response); echo '</pre>'; exit;

      // Decode the response.
      $jsonObj = json_decode($response);

      $result = [];
      foreach($jsonObj->d->results as $value) {
        switch ($value->__metadata->type) {
          case 'WebResult':
            if (!strpos($value->Url, '/lambda/')) {
              $url = self::transform_url($value->Url);
              $title = $value->Title;
              $description = $value->Description;
              if ($local && $url != $value->Url) {
                $description .= '<br/>original url: ' . htmlspecialchars($value->Url);
              }
              $result[] = [
                'url' => $url,
                'title' => $title,
                'description' => $description
              ];
            }
            break;
        }
      }
      return $result;
    }

    public static function transform_url($url) {
      $url = str_replace('https://', 'http://', $url);
      $prefix = 'http://' . $_SERVER['HTTP_HOST'] . '/';
      $prefix2 = 'http://www.winwrap.com/';
      if (substr($url, 0, strlen($prefix2)) != $prefix2) return $url;
      $url = substr($url, strlen($prefix2));
      $parts = explode('?', $url, 2);
      $url = $parts[0];
      $query = count($parts) == 2 ? $parts[1] : '';

      //return 'x:' . $url;
      if ($url != 'web/' || substr($query, 0, 3) != 'ann' && substr($query, 0, 3) != 'sol') {
        if (substr($url, 0, 10) != 'web/basic/') return $prefix . $url;
        if (substr($url, 0, 18) == 'web/basic/support/') return $prefix . $url;
      }

      $parts = explode('/', $url);
      if ($parts[count($parts)-1] == 'default.asp') $parts[count($parts)-1] = '';
      if (count($parts) == 1 && substr($query, 0, 4) != 'ann=' && substr($query, 0, 4) != 'sol') return $prefix . $url;
      $url = implode('/', $parts);
      $query = str_replace('menu=yes&', '', $query);
      $query = str_replace('&menu=yes', '', $query);
      $query = str_replace('menu=yes', '', $query);
      $bare_url = $url;
      if ($bare_url[strlen($bare_url)-1] == '/') $bare_url = substr($bare_url, 0, strlen($bare_url)-1);
      if ($query) $url .= '?' . $query;
      $new_url = '';
      $x = strpos($url, '&');
      $url1 = $x ? substr($url, 0, $x) : $url;
      // url through first parameter transformations
      $url2 = $url1;
      $url2 = preg_replace('/^web\/\?ann=(.*)/', 'web2/news/$1', $url2);
      $url2 = preg_replace('/^web\/\?sol=(.*)/', 'web2/solution/$1', $url2);
      $url2 = preg_replace('/^web\/basic\/history\/\?ann=(.*)/', 'web2/news/$1', $url2);
      $url2 = preg_replace('/^web\/basic\/solutions\/\?sol=(.*)/', 'web2/solution/$1', $url2);
      $url2 = preg_replace('/^web\/basic\/history\/\?announcement=(.*)/', 'web2/news/$1', $url2);
      $url2 = preg_replace('/^web\/basic\/solutions\/\?solution=(.*)/', 'web2/solution/$1', $url2);
      $url2 = preg_replace('/^web\/basic\/license\/\?doc=cert(.*)/', 'web2/license-certs$1', $url2);
      $url2 = preg_replace('/^web\/basic\/language\/\?p=(.*)/', 'web2/basic/#!/ref/WWB-$1', $url2);
      $url2 = preg_replace('/^web\/basic\/reference\/\?p=doc(.*)/', 'web2/basic/#!/ref/COM-doc$1', $url2);
      $url2 = preg_replace('/^web\/basic\/reference\/\?p=pop(.*)/', 'web2/basic/#!/ref/COM-pop$1', $url2);
      $url2 = preg_replace('/^web\/basic\/reference\/\?p=WPF-(.*)/', 'web2/basic/#!/ref/WPF-$1', $url2);
      $url2 = preg_replace('/^web\/basic\/reference\/\?p=AZW-(.*)/', 'web2/basic/#!/ref/AZW-$1', $url2);
      $url2 = preg_replace('/^web\/basic\/reference\/\?p=ASP-(.*)/', 'web2/basic/#!/ref/AZW-$1', $url2);
      $url2 = preg_replace('/^web\/basic\/reference\/\?p=(.*)/', 'web2/basic/#!/ref/NET-$1', $url2);
      if ($url2 != $url1) {
        $new_url = $url2;
      } else {
        // url through first parameter map
        $map = [
          'web/basic/platforms/?p=com' => 'web2/basic/#!/ref/COM-doc0001.htm',
          'web/basic/platforms/?p=net' => 'web2/basic/#!/ref/NET-WinWrap.Basic.html',
          'web/basic/platforms/?p=wpf' => 'web2/basic/#!/ref/WPF-WinWrap.Basic.html',
          'web/basic/platforms/?p=azw' => 'web2/basic/#!/ref/AZW-WinWrap.Basic.html',
          'web/basic/platforms/?p=asp' => 'web2/basic/#!/ref/AZW-WinWrap.Basic.html',
          'web/basic/order/?cat=WWXX41' => 'web2/order-form/WWXX41',
          'web/basic/order/?cat=WWXX42' => 'web2/order-form/WWXX42',
          'web/basic/order/?cat=WWXX60' => 'web2/order-form/WWXX60'
        ];
        if (isset($map[$url1]))
          $new_url = $map[$url1];

        if (!$new_url) {
          // url without parameters map
          $bare_map = [
            'web/basic/sernum_revoked.asp' => 'no',
            'web/basic/cert_revoked.asp' => 'no',
            'web/basic/download2.asp' => 'no',
            'web/basic' => 'web2',
            'web/basic/history' => 'web2/news',
            'web/basic/history/history.asp' => 'web2/history',
            'web/basic/history/enhancements.asp' => 'web2/enhancements',
            'web/basic/history/converting.asp' => 'web2/solution/saxbasic',
            'web/basic/history/converting7.asp' => 'web2/solution/saxbasic',
            'web/basic/history/cypress-enable.asp' => 'web2/news/cypress-enable',
            'web/basic/history/subscribe.asp' => 'web2/evaluate', // change to subscribe
            'web/basic/products' => 'web2/how-to-buy',
            'web/basic/evaluate.asp' => 'web2/evaluate',
            'web/basic/license' => 'web2/asset/doc/all/distrib2015.pdf',
            'web/basic/order' => 'web2/how-to-buy',
            'web/basic/platforms' => 'web2/basic/#!/ref/doc-references',
            'web/basic/screenshots' => 'web2/screenshots',
            'web/basic/screenshots/developer' => 'web2/screenshots',
            'web/basic/screenshots/user' => 'web2/screenshots',
            'web/basic/language' => 'web2/basic/#!/ref/WWB-doc0001.htm',
            'web/basic/reference' => 'web2/basic/#!/ref/doc-references',
            'web/basic/solutions' => 'web2/solutions',
            'web/basic/search.asp' => 'web2/search'
          ];
          if (isset($bare_map[$bare_url]))
            $new_url = $bare_map[$bare_url];
          if ($new_url == 'no') return $url;
        }
      }
      if ($new_url)
          $url = $new_url;
      return $prefix . $url;
    }

  }

?>
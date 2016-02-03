<?php
  
  class Reference {

    public static function navigation() {
      require_once(REF . "doc2/toc.php"); // WinWrap Basic for .NET
      require_once(REF . "doc4/toc.php"); // WinWrap Basic for WPF
      require_once(REF . "doc6/toc.php"); // WinWrap Basic for Azure Web Sites
//      require_once(REF . "doc5/toc.php"); // WinWrap Basic for ASP.NET/IIS
      require_once(REF . "wwbcomhtm/toc.php"); // WinWrap Basic for COM
      require_once(REF . "query/toc.php"); // Query

      // inject query toc
      $dev_com = self::inject_toc('COM', $dev_com, $dev_query);
      $dev_net = self::inject_toc('NET', $dev_net, $dev_query);
      $dev_wpf = self::inject_toc('WPF', $dev_wpf, $dev_query);
      $dev_azw = self::inject_toc('AZW', $dev_azw, $dev_query);

      require_once(REF . "ww10_000htm/toc.php"); // WWB-COM and WWB.NET languages

      // WWB-COM and WWB.NET languages adjustment: remove top-level parent and IDE
      for ($i = 0; $i < count($language); ++$i) {
        if ($language[$i]['name'] == 'IDE Overview') {
          $language = array_slice($language, 1, $i);
          break;
        }
      }
      $language[0]['parent_id'] = 0;
      $language[0]['name'] = 'WinWrap&reg; Basic Languages';

      $id = 999999;

      $reference = [['id' => $id, 'name' => 'WinWrap&reg; Basic References', 'link' => 'doc-references', 'parent_id' => 0]];
      $dev_net[0]['parent_id'] = $id;
      $dev_azw[0]['parent_id'] = $id;
//      $dev_asp[0]['parent_id'] = $id;
      $dev_wpf[0]['parent_id'] = $id;
      $dev_com[0]['parent_id'] = $id;

      $flat = array_merge($language, $reference, $dev_com, $dev_net, $dev_wpf, $dev_azw);

      return $flat;
    }

    // only injects on the first occurrence
    private static function inject_toc($group, $toc, $inject) {
      $parent_id = 0;
      $max_id = 0;
      foreach ($toc as $entry) {
        $max_id = $entry['id'];
        if ($entry['name'] == 'Query Method') {
          $parent_id = $max_id;
          break;
        }
      }
      if ($parent_id) {
        foreach ($inject as $entry) {
          $temp = $entry;
          $temp['id'] += $max_id;
          if ($temp['parent_id'] == 0) {
            $temp['parent_id'] = $parent_id;
          } else {
            $temp['parent_id'] += $max_id;
          }
          $temp['link'] = $group . $temp['link'];
          $toc[] = $temp;
        }
      }
      return $toc;
    }

    public static function read_by_page($params) {
      //echo '<pre>'; debug_print_backtrace(); print_r($params); echo '</pre>'; exit;
      $file_name = isset($params['page']) ? $params['page'] : 'doc-basic';
      $link_style = isset($params['link_style']) ? $params['link_style'] : 2;
      $reference = self::read_file($file_name, $link_style);
      SiteStructure::set_page_title($reference['title']);
      return $reference;
    }

    // link_style:
    // 0: basic/#!/ref/<file_name>
    // 1: basic/?_escaped_fragement_=/ref/<file_name>
    // 2: basic/ref/?page=<file_name>
    public static function read_file($file_name, $link_style = 0) {
      //echo $file_name . ', ' . $link_style; exit;
      $parts = explode('-', $file_name, 2);
      $group = $parts[0];
      // remove / to prevent arbitrary file reading (Tom 1/5/15)
      $file = str_replace('/', '', $parts[1]);
      $dir_mapping = [
        'WWB' => 'ww10_000htm', // WWB-COM and WWB.NET languages
        'COM' => 'wwbcomhtm', // WinWrap Basic for COM
        'NET' => 'doc2', // WinWrap Basic for .NET
        'WPF' => 'doc4', // WinWrap Basic for WPF
        'ASP' => 'doc5', // WinWrap Basic for ASP.NET/IIS
        'AZW' => 'doc6' // WinWrap Basic for Azure Web Sites
      ];
      $heads = [
        'WWB' => 'Language',
        'COM' => 'For COM Reference (v10)',
      ];
      foreach (['COM', 'NET', 'WPF', 'AZW'] as $key) {
        $dir_mapping[$key . 'Q'] = 'query'; // Query documentation
        $dir_mapping[$key . 'F'] = 'query/Feature'; // Query feature documentation
        $heads[$key . 'Q'] = 'Query Command';
        $heads[$key . 'F'] = 'Query Feature';

      }

      switch ($link_style) {
        case -1: // # link (in an _escaped_fragement_ page)
        case 0: // # link
          $link = '/basic/#!/ref/';
          break;
        case 1: // escaped_fragment
          $link = '/basic/?_escaped_fragment_=/ref/';
          break;
        case 2: // test page
          $link = '/basic/ref/?page=';
          break;
      }

      if ($group == 'doc') {
        $doc = Doc::by_name('doc-' . $file);
        $doc['content'] = str_replace('/basic/#!/ref/', $link, $doc['content']);
        return $doc;
      }

      $file_name = REF . "{$dir_mapping[$group]}/$file";

      if (!file_exists($file_name)) {
        $format = '<div id="nsbanner" class="ng-scope">
          <div id="bannerrow1">
            <table class="bannerparthead" cellspacing="0">
              <tbody><tr id="hdr">
                <td class="runninghead">WinWrap&reg; Basic</td>
                <td class="product">
                </td>
              </tr>
            </tbody></table>
          </div>
          <div id="TitleRow">
            <h1 class="dtH1">Invalid</h1>
          </div>
          <div id="nstext">
            File "%s" does not exist.
          </div>
        </div>';
        return [
          'title' => 'Invalid',
          'content' => sprintf($format, $file)
        ];
      }

      $content = file_get_contents($file_name);

      $content = mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));

      if (isset($heads[$group])) {
        $format = '<div id="nsbanner" class="ng-scope">
          <div id="bannerrow1">
            <table class="bannerparthead" cellspacing="0">
              <tbody><tr id="hdr">
                <td class="runninghead">WinWrap&reg; Basic %s</td>
                <td class="product">
                </td>
              </tr>
            </tbody></table>
          </div>
          <div id="TitleRow">
            <h1 class="dtH1">%s</h1>
          </div>
        </div>';
        $title = '';
        $head = $heads[$group];
        $content = preg_replace_callback('/^.*?<font.*?>.*?<\/font>.*?<font.*?><b>(.*?)<\/b><\/font>.*?<hr\/>/s', function($matches) use ($group, $format, $head, &$title) {
          $title = $matches[1];
          return sprintf($format, $head, $title);
        }, $content);
      } else {
        // UTF-8 encoding (needed for chrome and opera)
        $content = str_replace('charset=Windows-1252', 'charset=utf-8', $content);
        preg_replace_callback('/class="dtH1">([^<]*)</', function($matches) use (&$title) {
          $title = $matches[1];
        }, $content);
      }

      $content = preg_replace('/<div id="footer">(.*?)<\/div>/', '', $content);

      $format = '<i class="fa fa-%s" title="%s"></i> ';
      $public = sprintf($format, 'user', 'Public');
      $protected = sprintf($format, 'key', 'Protected');
      $internal = sprintf($format, 'dot-circle-o', 'internal');
      $private = sprintf($format, 'briefcase', 'Private');
      $event = sprintf($format, 'bolt', 'Event');
      $property = sprintf($format, 'hand-o-left', 'Property');
      $method = sprintf($format, 'share-square-o', 'Method');

      // replace old-school image-icons with font-awesome icons
      $icons = [
        'pubevent' => "$public$event",
        'pubproperty' => "$public$property",
        'pubmethod' => "$public$method",
        'protproperty' => "$protected$property",
        'protmethod' => "$protected$method",
        'intproperty' => "$protected$internal$property",
        'intmethod' => "$protected$internal$method"
      ];
      $content = preg_replace_callback('/<pre class="code"><span class="lang">\[(.*?)\].*?<\/span>(.*?)<\/pre>/s', function($matches) {
        $comment = '\'';
        $lang = 'wwbnet';
        switch ($matches[1]) {
          case 'Visual Basic':
          case 'VB.NET':
            $lang = 'vbnet';
            break;
          case 'C#':
          case 'C++':
            $comment = '//';
            $lang = 'csharp';
            break;
        }
        return sprintf("<div data-md-code=\"{ lang: '%s' }\">%s %s\r\n%s</div>", $lang, $comment, $matches[1], $matches[2]);
      }, $content);
      $content = preg_replace_callback('/<img[^s]*src="([^.]*).gif"[^>]*>/', function($matches) use ($icons) {
        $icon = isset($icons[$matches[1]]) ? $icons[$matches[1]] : $matches[1];
        return "$icon &nbsp; &nbsp;";
      }, $content);

      // remove ms-help links
      $content = preg_replace('/<a href="ms-help:.*?>(.*?)<\/a>/', '<b>$1</b>', $content);
      // reformat links for front-end linking
      if ($link_style == 0) {
        $link = SUBDIR . $link;
      }
      $content = preg_replace('/href="((WinWrap|doc_|pop_|WWB-).*?)"/', "href=\"$link$group-$1\"", $content);
      if (strlen($group) == 3) {
        // change query links
        $content = preg_replace('/\/web\/basic\/reference\/query\/\?v=(com\&|net\&amp;)c=Feature/',
          $link . $group . 'Q-Feature.htm', $content);
        $content = preg_replace('/\/web\/basic\/reference\/query\/\?v=(com|net)/',
          $link . $group . 'Q-0Help.htm', $content);
      } else {
        $group3 = substr($group, 0, 3);
        $content = str_replace('[[GROUP]]', $group3, $content);
        $content = str_replace(['**com:True**', '**net:True**'], $group3 == 'COM' ? ['True', 'False'] : ['False', 'True'], $content);
      }

      // add registered trademarks to all instances of WinWrap
      $content = str_replace('WinWrap Basic', 'WinWrap&reg; Basic', $content);

      return [
        'title' => $title,
        'content' => $content
      ];
    }

  }

?>
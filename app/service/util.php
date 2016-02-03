<?php

  class Util {

    private static function list_format($list, $wrapper = FALSE) {
      if ($wrapper) {
        $mapped = array_map(function($item) use ($wrapper) {
          return sprintf($wrapper, $item);
        }, $list);
        
        return implode('', $mapped);
      } else {
        $count = 0;
        $mapped = array_map(function($item) use (&$count) {
          return ['id' => $count++, 'text' => $item];
        }, $list);
        return str_replace('"', "'", json_encode($mapped));
      }
    }

    public static function get_countries() {
      return ['(Select)','USA','Afghanistan','Aland Islands','Albania','Algeria','American Samoa','Andorra','Angola','Anguilla','Antarctica','Antigua and Barbuda','Argentina','Armenia','Aruba','Asia Pacific HQ','Australia','Austria','Azerbaijan','Bahamas, The','Bahrain','Baker Island','Bangladesh','Barbados','Belarus','Belgium','Belize','Benin','Bermuda','Bhutan','Bolivia','Bosnia and Herzegovina','Botswana','Bouvet Island','Brazil','British Indian Ocean Territory','Brunei','Bulgaria','Burkina Faso','Burundi','Cambodia','Cameroon','Canada','Cape Verde','Cayman Islands','Central African Republic','Chad','Chile','China','Christmas Island','Cocos &#40;Keeling&#41; Islands','Colombia','Comoros','Congo','Congo &#40;DRC&#41;','Cook Islands','Corp HQ-S&#38;M','Costa Rica','C&#244;te d&#8217;Ivoire','Croatia','Cuba','Cyprus','Czech Republic','Denmark','Djibouti','Dominica','Dominican Republic','Ecuador','Egypt','El Salvador','EMEA HQ','Equatorial Guinea','Eritrea','Estonia','Ethiopia','Falkland Islands &#40;Islas Malvinas&#41;','Faroe Islands','Fiji Islands','Finland','France','French Guiana','French Polynesia','French Southern and Antarctic Lands','Gabon','Gambia, The','Gaza Strip','Georgia','Germany','Ghana','Gibraltar','Greece','Greenland','Grenada','Guadeloupe','Guam','Guatemala','Guernsey','Guinea','Guinea-Bissau','Guyana','Haiti','Heard Island and McDonald Islands','Honduras','Hong Kong SAR','Howland Island','Hungary','Iceland','India','Indonesia','Iran','Iraq','Ireland','Isle of Man','Israel','Italy','Jamaica','Japan','Jarvis Island','Jersey','Johnston Atoll','Jordan','Kazakhstan','Kenya','Kingman Reef','Kiribati','Kuwait','Kyrgyzstan','L. America Other','Laos','LATAM HQ','Latvia','Lebanon','Lesotho','Liberia','Libya','Liechtenstein','Lithuania','Luxembourg','Macao SAR','Macedonia, Former Yugoslav Republic of','Madagascar','Malawi','Malaysia','Maldives','Mali','Malta','Marshall Islands','Martinique','Mauritania','Mauritius','Mayotte','Mexico','Micronesia','Midway Islands','Moldova','Monaco','Mongolia','Montenegro','Montserrat','Morocco','Mozambique','Myanmar','N. America Other','Namibia','Nauru','Navassa Island','Nepal','Netherlands','Netherlands Antilles','New Caledonia','New Zealand','Nicaragua','Niger','Nigeria','Niue','Norfolk Island','North Korea','Northern Mariana Islands','Norway','Oman','Pakistan','Palau','Palestinian Authority','Palmyra Atoll','Panama','Papua New Guinea','Paraguay','Peru','Philippines','Pitcairn Islands','Poland','Portugal','Puerto Rico','Qatar','Reunion','Romania','Russia','Rwanda','Saint Helena','Saint Kitts and Nevis','Saint Lucia','Saint Pierre and Miquelon','Saint Vincent and the Grenadines','Samoa','San Marino','S&#227;o Tom&#233; and Pr&#237;ncipe','Saudi Arabia','Senegal','Serbia','Seychelles','Sierra Leone','Singapore','Slovakia','Slovenia','Solomon Islands','Somalia','South Africa','South Georgia and the South Sandwich Islands','South Korea','Spain','Sri Lanka','Sudan','Suriname','Svalbard and Jan Mayen Island','Swaziland','Sweden','Switzerland','Syria','Taiwan','Tajikistan','Tanzania','Thailand','Timor-Leste','Togo','Tokelau','Tonga','Trinidad and Tobago','Tunisia','Turkey','Turkmenistan','Turks and Caicos Islands','Tuvalu','U.S. Minor Outlying Islands','Uganda','Ukraine','United Arab Emirates','United Kingdom','Uruguay','Uzbekistan','Vanuatu','Vatican City','Venezuela','Vietnam','Virgin Islands, British','Virgin Islands, U.S.','Wake Island','Wallis and Futuna','West Bank','Western Sahara','Yemen','Zambia','Zimbabwe','USA','Afghanistan','Aland Islands','Albania','Algeria','American Samoa','Andorra','Angola','Anguilla','Antarctica','Antigua and Barbuda','Argentina','Armenia','Aruba','Asia Pacific HQ','Australia','Austria','Azerbaijan','Bahamas, The','Bahrain','Baker Island','Bangladesh','Barbados','Belarus','Belgium','Belize','Benin','Bermuda','Bhutan','Bolivia','Bosnia and Herzegovina','Botswana','Bouvet Island','Brazil','British Indian Ocean Territory','Brunei','Bulgaria','Burkina Faso','Burundi','Cambodia','Cameroon','Canada','Cape Verde','Cayman Islands','Central African Republic','Chad','Chile','China','Christmas Island','Cocos &#40;Keeling&#41; Islands','Colombia','Comoros','Congo','Congo &#40;DRC&#41;','Cook Islands','Corp HQ-S&#38;M','Costa Rica','C&#244;te d&#8217;Ivoire','Croatia','Cuba','Cyprus','Czech Republic','Denmark','Djibouti','Dominica','Dominican Republic','Ecuador','Egypt','El Salvador','EMEA HQ','Equatorial Guinea','Eritrea','Estonia','Ethiopia','Falkland Islands &#40;Islas Malvinas&#41;','Faroe Islands','Fiji Islands','Finland','France','French Guiana','French Polynesia','French Southern and Antarctic Lands','Gabon','Gambia, The','Gaza Strip','Georgia','Germany','Ghana','Gibraltar','Greece','Greenland','Grenada','Guadeloupe','Guam','Guatemala','Guernsey','Guinea','Guinea-Bissau','Guyana','Haiti','Heard Island and McDonald Islands','Honduras','Hong Kong SAR','Howland Island','Hungary','Iceland','India','Indonesia','Iran','Iraq','Ireland','Isle of Man','Israel','Italy','Jamaica','Japan','Jarvis Island','Jersey','Johnston Atoll','Jordan','Kazakhstan','Kenya','Kingman Reef','Kiribati','Kuwait','Kyrgyzstan','L. America Other','Laos','LATAM HQ','Latvia','Lebanon','Lesotho','Liberia','Libya','Liechtenstein','Lithuania','Luxembourg','Macao SAR','Macedonia, Former Yugoslav Republic of','Madagascar','Malawi','Malaysia','Maldives','Mali','Malta','Marshall Islands','Martinique','Mauritania','Mauritius','Mayotte','Mexico','Micronesia','Midway Islands','Moldova','Monaco','Mongolia','Montenegro','Montserrat','Morocco','Mozambique','Myanmar','N. America Other','Namibia','Nauru','Navassa Island','Nepal','Netherlands','Netherlands Antilles','New Caledonia','New Zealand','Nicaragua','Niger','Nigeria','Niue','Norfolk Island','North Korea','Northern Mariana Islands','Norway','Oman','Pakistan','Palau','Palestinian Authority','Palmyra Atoll','Panama','Papua New Guinea','Paraguay','Peru','Philippines','Pitcairn Islands','Poland','Portugal','Puerto Rico','Qatar','Reunion','Romania','Russia','Rwanda','Saint Helena','Saint Kitts and Nevis','Saint Lucia','Saint Pierre and Miquelon','Saint Vincent and the Grenadines','Samoa','San Marino','S&#227;o Tom&#233; and Pr&#237;ncipe','Saudi Arabia','Senegal','Serbia','Seychelles','Sierra Leone','Singapore','Slovakia','Slovenia','Solomon Islands','Somalia','South Africa','South Georgia and the South Sandwich Islands','South Korea','Spain','Sri Lanka','Sudan','Suriname','Svalbard and Jan Mayen Island','Swaziland','Sweden','Switzerland','Syria','Taiwan','Tajikistan','Tanzania','Thailand','Timor-Leste','Togo','Tokelau','Tonga','Trinidad and Tobago','Tunisia','Turkey','Turkmenistan','Turks and Caicos Islands','Tuvalu','U.S. Minor Outlying Islands','Uganda','Ukraine','United Arab Emirates','United Kingdom','Uruguay','Uzbekistan','Vanuatu','Vatican City','Venezuela','Vietnam','Virgin Islands, British','Virgin Islands, U.S.','Wake Island','Wallis and Futuna','West Bank','Western Sahara','Yemen','Zambia','Zimbabwe'];
    }

    public static function get_payments() {
      return ['(Choose)','Wire Transfer','Purchase Order','Check','PayPal'];
    }

    public static function get_scripting() {
      return ['(Choose)','Unknown', 'New Project','proprietary scripting solution','Sax Basic','VBScript/MSScriptControl','Cypress Enable','Open Source (Python, etc.)','Other','None'];
    }

    /* @HTMLI
    *  params: wrapping_string (optional)
    *  returns: encoded array, optionally imploded into a string
    */
    public static function countries($context, $wrapper = FALSE) {
      return self::list_format(self::get_countries(), $wrapper);
    }

    /* @HTMLI
    *  params: wrapping_string (optional)
    *  returns: encoded array, optionally imploded into a string
    */
    public static function payments($context, $wrapper = FALSE) {
      return self::list_format(self::get_payments(), $wrapper);
    }

    /* @HTMLI
    *  params: wrapping_string (optional)
    *  returns: encoded array, optionally imploded into a string
    */
    public static function scripting($context, $wrapper = FALSE) {
      return self::list_format(self::get_scripting(), $wrapper);
    }

    public static function parse_html_interface() {
      $comments = [];
      $files =
        array_map(function($file) { return MD . $file; }, scandir(MD)) +
        array_map(function($file) { return MODEL . $file; }, scandir(MODEL)) +
        array_map(function($file) { return SERVICE . $file; }, scandir(SERVICE));
      foreach ($files as $file) {
        if ($file[0] != '.') {
          $content = file_get_contents($file);
          $class = '';
          preg_replace_callback("/class ([A-Z][A-z]*)/", function($matches) use (&$class) {
            $class = $matches[1];
          }, $content);
          preg_replace_callback("/@HT()MLI(.*?)(public|private).*?function ([^(]*)/s", function($matches) use (&$comments, &$class) {
            $comment = $matches[2];
            $params = [];
            preg_replace_callback("/params: (.*?)\*/s", function($matches) use (&$params) {
              $params = array_map('trim', explode(',', $matches[1]));
            }, $comment);
            $return = '';
            preg_replace_callback("/return(s)?: (.*?)\*/s", function($matches) use (&$return) {
              $return = trim($matches[2]);
            }, $comment);
            $comments[] = [
              'class' => $class, 
              'method' => $matches[4], 
              'parameters' => $params, 
              'returns' => $return
            ];
          }, $content);
        }
      }
      return $comments;
    }

  }

?>
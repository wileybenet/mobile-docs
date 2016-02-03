<?php

class GoogleMail {
  
  // toname, toemail, fromname, fromemail, subject, body
  public static function send($params = []) {
    $fields = $params;
    $fields['keyx'] = 'D84qx5';
    $ch = curl_init('http://email-support-winwrap.appspot.com/sendemail');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);  // DO NOT RETURN HTTP HEADERS
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // RETURN THE CONTENTS OF THE CALL
    $data = curl_exec($ch);
    curl_close($ch);
    //echo $data;
    if (strpos($data, '<p>You wrote:') == 0) {
      return ['error' => $data];
    }
    return true;
  }

}

?>
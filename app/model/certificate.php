<?php

  class Certificate {

    private $data = [];

    public static function read_by_get_key() {
      $key = $_GET['Key'];
      $certificate = new Certificate();
      $evaluator = Evaluator::read_by_key($key);
      $certificate->set_by_evaluator($evaluator, false);
      $certificate->adjust();
      return $certificate->get_data();
    }

    public function get_data() {
      return $this->data;
    }

    public function set_by_evaluator($evaluator, $isnew) {
      // fields needed for signing
      $this->data['CertCreateDateTime'] = $evaluator['created'];
      $this->data['CertKey'] = $evaluator['key'];
      $this->data['CertProduct'] = 1; // WinWrap Basic
      $this->data['CertFormat'] = 2;
      $this->data['CertKind'] = 1; // Evaluation
      $this->data['CertTest'] = 1;
      $this->data['CertVersion'] = count($evaluator['downloads']) + ($isnew ? 1 : 0);
      $this->data['CertName'] = $evaluator['company'];
      $this->data['CertDesc'] = $evaluator['name'];
      $this->data['CertUrl'] = $evaluator['email'];
      $this->data['CertPlatform'] = 0x7ffffff; // all platforms
      $this->data['CertOS'] = 0x7ffffff; // all OSs
      $this->data['CertOption'] = 0xffffff; // all options
      $this->data['CertExpirationDate'] = Util2::future_date($evaluator['agreement_date'], 60, $evaluator['expiration_date']);
      $this->data['RevokedDate'] = null;
      $this->data['ReissuedVersion'] = 0;
      $this->data['EncryptionKey1'] = $evaluator['key'];
      $this->data['EncryptionKey2'] = $evaluator['created'];
      // fields specific to evaluators
      $this->data['EvaluatorName'] = $evaluator['name'];
      $this->data['EvaluatorEmail'] = $evaluator['email'];
      $this->data['EvaluatorCompany'] = $evaluator['company'];
      $this->data['EvaluatorCompanyUrl'] = self::fix_url($evaluator['url']);
    }

    private static function fix_url($url) {
      if (substr(strtolower($url), 0, 5) != 'http://') {
        $url = 'http://' . $url;
      }
      $url = str_replace('"', '', $url);
      return $url;
    }

    private function get_kind_name() {
      $a = ['Invalid', 'Evaluation', 'Development', 'Server', 'Application', 'Permission', 'Encryption', 'Decryption', 'Master'];
      return $a[$this->data['CertKind']];
    }

    private function get_kind_name2() {
      $certkind = $this->get_kind_name();
      if ($this->is_azw())
        $certkind = "Azure Website $certkind";
      else if ($this->is_aspnet())
        $certkind = "ASP.NET/IIS+$certkind";
      
      return $certkind;
    }

    private function is_aspnet() {
      if (isset($this->data['PartNo'])) {
        switch (substr($this->data['PartNo'], 0, 6)) {
        case 'WWXX60':
        case 'WWAC23':
        case 'WWAC31':
        case 'WWAC42':
          return true;
        }
        
        switch ($this->data['FeeKind']) {
        case 60: // WWXX60
        case 423: // WWAC23
        case 431: // WWAC31
        case 442: // WWAC42
          return true;
        }
        
        $v = explode("/", $this->data['CertExtra']);
        if (count($v) == 2)
          return true;
      }

      return false;
    }

    private function is_azw() {
      if (isset($this->data['PartNo'])) {
        switch (substr($this->data['PartNo'], 0, 6)) {
        case 'WWXX60':
        case 'WWAC23':
          return true;
        }
        
        switch ($this->data['FeeKind']) {
        case 60: // WWXX60
        case 423: // WWAC23
          return true;
        }
      }
      
      return false;
    }

    private function decode_application() {
      if (!isset($this->data[CertExtra]))
        return '(nothing)';

      return str_replace('|', ', ', $this->data[CertExtra]);
    }

    private static function expanded($b, $a) {
      for ($j = 0; $j < 31; ++$j) {
        if (($b&1) && isset($a[$j]))
          $x[] = $a[$j];

        $b >>= 1;
      }

      if (!isset($x))
        return '';

      return implode($b&0x80000000 ? ' or ' : ', ', $x);
    }

    private function get_platform_text() {
      switch ($this->data['CertKind']) {
      case 1: // Evaluation
      case 3: // Server
      case 4: // Application
        return self::expanded($this->data['CertPlatform'], ['COM', 'NET', 'WPF']);
      }
      return null;
    }

    private function get_os_text() {
      switch ($this->data['CertKind']) {
      case 1: // Evaluation
      case 3: // Server
      case 4: // Application
        return self::expanded($this->data['CertOS'], ['Win32', 'Win64']);
      }
      return null;
    }

    private function get_option_text() {
      // $this->data['PartNo']
      switch ($this->data['CertKind']) {
      case 1: // Evaluation
      case 3: // Server
      case 4: // Application
        return self::expanded($this->data['CertOption'], ['WWB.NET', 'WWB.NET/Compiled', 'Encryption', 'Decription']);
      }
      return null;
    }

    private function get_secret_as_guid() {
      $secret = $this->data['Secret'];
      return '{' . substr($secret, 0, 8) . '-' .
              substr($secret, 8, 4) . '-' .
              substr($secret, 12, 4) . '-' .
              substr($secret, 16, 4) . '-' .
              substr($secret, 20, 12) . '}';
    }

    private function get_days_left_text() {
      $daysleft = Util2::days_left(gmdate('Y-m-d'), $this->data['CertExpirationDate']);
      if ($daysleft <= 0)
        return 'expired';

      $s = $daysleft . ' day';
      if ($daysleft != 1)
        $s .= 's';

      $s .= ' left';
      return $s;
    }

    private function get_expires_text() {
      if (is_null($this->data['CertExpirationDate']))
        return 'never';

      return $this->data['CertExpirationDate'] . ' (' . $this->get_days_left_text() . ')';
    }

    private static function get_private_key() {
      return <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIIEogIBAAKCAQEA4dABIGg7XWdc0JwESvlr/4seixtJtWSrrymp0FQh76aGjuJu
1P/AbjzvDe1b8uujID5zt5WuQQwhlgP/tgMB4Dk75bMUcrMaPpPOACm4v9NaA+Ko
4+tfxt1V5bgVFErOtREbiIck8ObJA4M+vlTrZXIXRwUk1DT1hsxnCFGizuRYUFLj
AA9VroA0HvpuApFibZtnRIBr7tXHQsuV8NVt+qh9u2X941Uc2R1q6fTX6QiDWvAT
dzmPl48KNQojsgyl/iT3nFShc/jhe60EHNwkCy0a/VJweyDUttCHdHum5YA4ku0U
yBoQfdInmoELGjEiJcBwIU3aD7/68Ub4M9cr/wIBIwKCAQA6EOpYylhoeaonA4wT
R3OK2qF0OjeGalgIeG1+toxiMiKZxTJxSRQ5mqPfAoVbuO9usPkvNR4t+84fQtta
sFDwg77x7Dh1Q/9vLVI6jmK0+9VRdMxX3XBmViwH3uDZVRCU9cVAXUQDbot17ox6
FdYhZnsDojyrmJboJe6bvTh+V32P4QBBi1ESEVgSC1DDJsXkG6U3AQ1QmqzzHK8A
Xzd9YpPIYso/qXdtSwzta5v3orVfp/sRGfvro2wyLY3gCikejV6gwzS4ENRR4Yh4
PnkU1nJHh+8/ZGfuC0V+vLnf8ExLSKtlL529aAkY2e5PgF+/FuQYWcXfeqL9SZoB
zLxrAoGBAP/ZTWdCaas7e8Mei3rqlANjSRj0n/HOgAXgrmb/daql2kjr0XVazzn5
VY285WyXVGZBpvvODYbFivka+Fq/5TBUYueXWsGMF3aINbgBMm/0MSQywzMbUf2j
o0v1jcELQ2W5Jb978ilo2REjoylRCLavMM6lCc2I59yWM9LNbyyvAoGBAOHyKLOi
Tv1lpIxIe9V2ZpZaWJLrv/EDUZKUO9u3CeuJpC79MMtYD0tTMjkf+yk5V82I2EdK
OMNaFsGsRC9ucblPY2ciAr2N2Nvt7cedYWtwnY+0YikwZNEgHdfnZUvlJI21AhAX
/PNtSA+IACaT/Exy/birynzMbPnXZrIKEumxAoGAHT1n7os/RsT4M452VzDAdWpu
wQYDpptB1Mk4gMy8/Y9MJZdLI1rV2r1o3P+lMPtaGlClXps0vvIBQQplhrbYXUt5
BIZTg9V/Bjtz2oPLP/6mh8tJgi8CDlvYJfAtdSXb0Rx5V7Zk0YhTUmp5DAlC0wy1
Hu5KQ2Aaf5wjLgjZgXMCgYEAmu8x2kNpXU0DHl2WvkKPfQrAZL7qBFoMDL1q3834
LHujyHMaJQkvDxR6NczJe1qFWbzsE6Cb86t9UZqyaarRo6Qm56JLBaMYXEthOGvr
B9gxhxybFO37/R1WSufQbo5/dx0IupQbKpQUJ+g6nhxVO7yQuSVXpgiFPZsEl1dd
bQsCgYEA9Rz8CDwtqW3XTPk1tmimGxBLUDqTtoPAdAf1n3cp8gBSmEdyQ1brE1ux
sPPyb20ioSFvaChsKI7KigbODU1YDtHiIZPjoU805iBWdU4zdWwuQ5+Doo74t3ZJ
J4mHiNpFHbissVubIXJ4Npp23/51KA7IY8+R4Vkm4IFvLMLCrmc=
-----END RSA PRIVATE KEY-----
EOD;
    }

    private function get_signature($msg) {
      $secret1 = 'kadfT8pD';
      $secret2 = '8kH&L3#H';
      $msg = $secret1 . $msg . $secret2;
      $hash = $this->hash($msg);
      openssl_private_encrypt($hash, $signature, self::get_private_key());
      return base64_encode($signature);
    }

    private function hash($s) {
      return sha1($s, $this->data['CertFormat'] > 1);
    }

    private function hash_field($s) {
      if (!$s)
        return $s;

      $secret = 'i8^D/.a0';
      $s = $secret . $s;
      return $this->hash($s);
    }

    private function encryption_passwords() {
      $secret1 = 'j*9Lq~h4';
      $secret2 = '9Bn]i#*L';
      return [
        'OkPDaSZF!c*[rOgODkUB{/1D-gzZ#?%',
        $secret1 . $this->data['EncryptionKey1'],
        'P/?f>o{se=z,&kRZ{`s^?*P6<wT_+OD',
        $secret2 . $this->data['EncryptionKey2'],
        '[bl.WrwZe$_}>V=1*sqg/?(`]nTKG<L'
      ];
    }

    private static function decrypt($s, $password) {
      $hash = substr(md5($password, true), 0, 8);
      $s .= str_repeat('\0', 8-strlen($s)%8);
      $d = mcrypt_cbc(MCRYPT_DES, $hash, $s, MCRYPT_DECRYPT, str_repeat('\0', 8));
      return substr($d, 1, ord($d[0]));
    }

    private function decrypt_field($s) {
      if (!$s)
        return '';

      $s = base64_decode($s);
      foreach (array_reverse(encryption_passwords()) as $password) {
        $s = self::decrypt($s, $password);
      }
      return $s;
    }

    private static function encrypt($s, $password) {
      $hash = substr(md5($secret . $password, true), 0, 8);
      $s = chr(strlen($s)) . $s;
      $s .= str_repeat("\0", 8-strlen($s)%8);
      return mcrypt_cbc(MCRYPT_DES, $hash, $s, MCRYPT_ENCRYPT, str_repeat('\0', 8));
    }

    private function encrypt_field($s) {
      if (!$s)
        return $s;

      foreach (encryption_passwords() as $password) {
        $s = self::encrypt($s, $password);
      }
      return base64_encode($s);
    }

    private function get_link() {
      $link = 'http://' . $_SERVER['HTTP_HOST'] . SUBDIR;
      switch ($this->data['CertKind']) {
      case 1: // Evaluation
        $link .= '/evaluate/certificate?&';
        break;
      default:
        $link .= '/c.php?&';
        break;
      }

      foreach ($this->data as $field => $value) {
        if (substr($field, 0, 4) == 'Cert') {
          $name = substr($field, 4);
          switch ($name) {
          case 'Passphrase':
            $value = hash_field($value);
          case 'Extra':
            $value = $this->encrypt_field($value);
            break;
          }

          if ($value)
            $link .= $name . '=' . urlencode($value) . '&';
        }
      }

      return $link;
    }

    public function sign() {
      switch ($this->data['CertKind']) {
      case 8: // Master
        break;
      default:
        $this->data['CertSignedDate'] = gmdate('Y-m-d');
        break;
      }

      $link = $this->get_link();
      $secret = isset($this->data['Secret']) ? $this->data['Secret'] : '';

      $html = '<html>\r\n' .
        '<head>\r\n' .
        '<title>WinWrap&reg Basic ' . $this->get_kind_name2() . ' Certificate</title>\r\n' .
        '<meta http-equiv="refresh" content="0;url=' . $link . '">\r\n' .
        '</head>\r\n' .
        '<body>\r\n' .
        'Click <a href="' . $link. '">here</a> if page does not automatically refresh.\r\n' .
        '<a href="?&Signature=' . urlencode($secret) . '&"></a>\r\n' .
        '</body>\r\n' .
        '</html>\r\n';
      $html = "\xef\xbb\xbf" . str_replace('\r\n', "\r\n", $html);

      // calculate signature with secret in the html
      $signature = $this->get_signature($html);
      // replace secret with signature
      $html = str_replace('&Signature=' . urlencode($secret) . '&',
                  '&Signature=' . urlencode($signature) . '&', $html);
      // WinWrap Basic windows run-time will reverse this process and verify the signature
      return $html;
    }

    public function adjust() {
      $kind = strtolower($this->get_kind_name());
      $this->data['Label'] = $this->get_kind_name2();
      if ($this->data['CertTest'] && $this->data['CertKind'] != 1)
        $this->data['Label'] .= " (Test)";

      $this->data['Label'] .= " Certificate";

      $certreissue = false;
      $certnotexpired = '';
      if (!is_null($this->data['RevokedDate'])) {
        $this->data['Status'] = "Revoked";
        $this->data['Class'] = "ww_cert_revoked";
        $this->data['Title'] = "revoked";
        $certnotexpired = "No (revoked)";
        $this->data['Expires'] = $this->get_expires_text() . ", revoked";
        $this->data['Valid'] = false;
      } else if ($this->data['CertVersion'] < $this->data['ReissuedVersion']) {
        $this->data['Status'] = "Revoked";
        $this->data['Class'] = "ww_cert_revoked";
        $this->data['Title'] = "revoked, use reissued certificate instead";
        $certnotexpired = "No (revoked)";
        $this->data['Expires'] = $this->get_expires_text() . ", revoked";
        $this->data['Valid'] = false;
        $certreissue = true;
      } else {
        $this->data['Status'] = isset($this->data['CertSignedDate']) ? "Issued" : "Unissued";
        $this->data['Class'] = "ww_cert_$kind";
        switch ($this->data['CertKind']) {
        case 1: // Evaluation
          $this->data['Status'] = "Evaluation";
          break;
        default:
          if ($this->data['CertTest']) {
            $this->data['Status'] = "Test";
            $this->data['Class'] .= "_test";
          }
        }

        $devexpires = '';
        if (!$this->data['CertTest']) {
          switch ($this->data['CertKind']) {
          case 3: // Server
            if (!isset($this->data['CertExtra']))
              break;
          case 4: // Application
          case 7: // Decryption
            $devexpires = " on development machines";
            break;
          }
        }

        if ($this->data['CertExpirationDate'] > gmdate('Y-m-d')) {
          $this->data['DaysLeft'] = $this->get_days_left_text();
          $this->data['Expires'] = $this->get_expires_text();
          $this->data['Title'] = "$kind period has {$this->data['DaysLeft']}";
          $certnotexpired = "Yes (will expire$devexpires)";
          $this->data['Valid'] = true;
        } else {
          if (!$certreissue)
            $this->data['Status'] = "Expired";

          $this->data['Class'] = "ww_cert_expired";
          $this->data['Title'] = "$kind period has expired";
          if ($certreissue)
            $this->data['Title'] .= ", download the latest version";

          $certnotexpired = "No (expired$devexpires)";
          $this->data['Expires'] = $this->get_expires_text();
          $this->data['Valid'] = false;
        }
      }

      $this->data['PlatformText'] = $this->get_platform_text();
      $this->data['OSText'] = $this->get_os_text();
      $this->data['OptionText'] = $this->get_option_text();

      $this->data['AllowDesigning'] =
        ($this->data['CertKind'] <= 2 || $this->data['CertKind'] == 8 ? $certnotexpired : "No (Development certificate required)"); 
      $this->data['AllowRunning'] =
        $this->data['CertKind'] == 1 ? $certnotexpired :
        ($this->data['CertKind'] == 3 || $this->data['CertKind'] == 4 ? $certnotexpired : "No (Application or Server certificate required)");
      $this->data['AllowEditingDebugging'] =
        $this->data['CertKind'] == 1 ? $certnotexpired :
        ($this->data['CertKind'] == 4 ? $certnotexpired : "No (Application certificate required)");
      $this->data['Distribution'] =
        $this->data['CertTest'] || $this->data['CertKind'] == 2 || $this->data['CertKind'] == 5 || $this->data['CertKind'] == 6 || $this->data['CertKind'] == 8 ? "No (do not ship this file)" : "Yes";
    }
  }

?>

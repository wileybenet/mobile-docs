<?php

  class EvaluateCtrl extends Ctrl {

    public function evaluate($params = []) {
      $params['scripting'] = Util::get_scripting()[intval(@$params['scriptingi'])];
      $model = Record::allow($params, ['name', 'email', 'phone', 'company', 'url', 'scripting']);
      $where = [implode(' = ? AND ', array_keys($model)) . ' = ?'] + $model;
      $evaluator = Evaluator::read(['*'], TRUE, $where);
      $today = gmdate('Y-m-d');
      $update = [
        'agreement_date' => $today,
        'email_date' => $today,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'opt_out' => 0
      ];
      if (isset($evaluator['id'])) {
        Evaluator::update($update, $evaluator['id']);
        $evaluator += $update;
      } else {
        $model['`key`'] = Evaluator::get_unique_key($model);
        $model += $update;
        $evaluator = Evaluator::create($model);
        $evaluator['key'] = $evaluator['`key`'];
        unset($evaluator['`key`']);
      }
      $body = Template::render_doc_by_name('evaluate-email', $evaluator);
      $args = [
        'toname' => $evaluator['name'],
        'toemail' => $evaluator['email'],
        'fromname' => 'WinWrap Support',
        'fromemail' => 'support@mail.winwrap.com',
        'subject' => 'WinWrap Basic Evaluation',
        'body' => $body
      ];
      if (GoogleMail::send($args) === true) {
        unset($evaluator['id']);
        Render::json($evaluator);
      } else {
        header('Status: 500');
      }
    }

    public function download($params = []) {
      //echo '<pre>'; print_r($params); echo '</pre>'; exit;
      $setups_map = [
        'NET' => ['setup-4.0.msi' => 'WinWrap-NET4.0-setup.msi'],
        'WPF' => ['setup-4.0-wpf.msi' => 'WinWrap-WPF4.0-setup.msi'],
        'AZW' => ['setup-4.0-azw.msi' => 'WinWrap-AZW4.0-setup.msi'],
        'COM' => ['setup32.msi' => 'WinWrap-COM32-setup.msi',
                  'setup64.msi' => 'WinWrap-COM64-setup.msi']
      ];
      $all = [];
      foreach ($setups_map as $setup_map) {
        $all += $setup_map;
      }
      //$setups_map['ALL'] = $all; // disable for now (1/8/15)
      // select the platform's setup_map
      $platform = $params['platform'];
      $setup_map = $setups_map[$platform];
      if (!isset($setup_map)) {
        error_404();
      }
      // select the evaluator
      $key = $params['key'];
      $evaluator = Evaluator::read_by_key($key);
      //Render::json($evaluator); exit; // debug
      $error = false;
      if (!isset($evaluator['id'])) {
        $error = 'Invalid evaluator key.';
      } else if (Util2::expired($evaluator['agreement_date'], 60, $evaluator['expiration_date'])) {
        $error = 'Evaluation period has expired.';
      }
      if ($error) {
        Render::html(Doc::by_name('message')['content'], ['title' => 'Download Request Failed', 'message' => $error]);
        return;
      }
      // create evaluator cerificate
      $readme = Template::render_doc_by_name('evaluate-readme');
      $certificate = new Certificate();
      $certificate->set_by_evaluator($evaluator, true);
      // add download record
      $version = file_get_contents(EVALDIR . 'version.txt');
      $model = [
        'evaluator_id' => $evaluator['id'],
        'ip' => $_SERVER['REMOTE_ADDR'],
        'platform' => $platform != 'ALL' ? $platform : implode(',', array_keys($setups_map)),
        'version' => $version
      ];
      $download = EvaluatorDownload::create($model);
      // create signed certificate
      $html = $certificate->sign();
      //Render::text($_SERVER['HTTP_HOST'] . SUBDIR . "\r\n" . $html); exit;
      // create a temporary file
      $path = tempnam("tmp", "zip");
      $zipname = 'WinWrap-Basic-Evaluation-' . $platform;
      $zip = new ZipArchive();
      $okay = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
      $okay |= $zip->addEmptyDir($zipname);
      $okay |= $zip->addFromString($zipname . '/readme.htm', $readme);
      $okay |= $zip->addFromString($zipname . '/Evaluation.htm', $html);
      $okay |= $zip->addFromString($zipname . '/version.txt', $version);
      foreach ($setup_map as $setup => $name) {
        $okay |= $zip->addFile(EVALDIR . $setup, $zipname . '/' . $name);
      }
      $okay |= $zip->close();
      if (!$okay) {
        Render::text("ZipArchive failed.");
        return;
      }
      //$contents = file_get_contents($path);
      //file_put_contents(TEMPDIR . 'x.zip', $contents);
      // send zip file
      if (true) {
        header('Cache-Control: max-age=288000');
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        //header("Content-Type: application/force-download");
        header('Content-Length: ' . filesize($path));
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="' . $zipname . '.zip"');
        ob_end_flush();
        readfile($path);
      }
      unlink($path);
      // set download date
      $update = [
        'download_date' => gmdate('Y-m-d')
      ];
      EvaluatorDownload::update($update, $download['id']);
      // done
      exit();
    }

    public function installed($params = []) {
      $key = $params['key'];
      $version = $params['version'];
      $status = isset($params['Status']) ? $params['Status'] : '';
      $evaluator = Evaluator::read_by_key($key);
      if (isset($evaluator['downloads']) && $version >= 1 && $version <= count($evaluator['downloads'])) {
        // downloads are in reverse order (newest to oldest)
        // version number 1 is the oldest
        $idx = count($evaluator['downloads'])-$version;
        $id = $evaluator['downloads'][$idx]['id'];
        $update = [
          'installed_date' => gmdate('Y-m-d'),
          'status' => $status
        ];
        EvaluatorDownload::update($update, $id, 'installed_count = installed_count+1');
      }

      // show the news page
      Render::html(Doc::by_name('news')['content']);
    }

    public function banner($params = []) {
      $key = $params['key'];
      $values = explode('-', $key);

      $model['ip'] = $_SERVER['REMOTE_ADDR'];
      if ($model['ip'] != '99.197.188.96') {
        for ($i = 1; $i <= count($values); ++$i) {
          if ($i > 3) {
            break;
          }
          $model['data' . $i] = $values[$i-1];
        }

        Bannerhit::create($model);
      }

      // show the banner
      $file_name = IMAGES . 'winwrap-banner.png';
      $contents = file_get_contents($file_name);
      header('Content-Type: image/png');
      header('Content-Length: ' . strlen($contents));
      echo $contents;
      exit;
    }
  }

?>

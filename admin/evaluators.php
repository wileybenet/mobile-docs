<div class="container white">
  <div class="content left">
    <table class="data">
      <thead>
        <th>Evaluator/Email/Phone/Key</th>
        <th>Company/URL/Script</th>
        <th>Date/IP</th>
        <th>Platform</th>
        <th>Version</th>
        <th>Download</th>
        <th>Installed</th>
      </thead>
      <tbody>
        <?php
          $evaluators = Evaluator::read(['*'], FALSE, ['email_date > CURRENT_DATE - INTERVAL 2 MONTH'], 'email_date DESC');
          foreach ($evaluators as $evaluator) {
            $downloads = EvaluatorDownload::read_by_evaluator_id($evaluator['id']);
            $downs = [];
            if (is_array($downloads)) {
              foreach ($downloads as $download) {
                if ($download['download_date']) {
                  $installed = $download['installed_count'] > 0 ?
                    $download['installed_date'] . '(' . $download['installed_count'] . ')' :
                    '?';
                  $downs[] = [
                    'platform' => $download['platform'],
                    'version' => $download['version'],
                    'download' => $download['download_date'],
                    'installed' => $installed
                  ];
                }
              }
            }
            if (count($downs) == 0) {
              $downs[] = ['platform' => '', 'version' => '', 'download' => '', 'installed' => ''];
            }
            echo '<tr>';
            foreach (['name/email/phone/key', 'company/url/scripting', 'email_date/ip'] as $field) {
              $fields = explode('/', $field);
              $values = [];
              foreach ($fields as $fieldx) {
                $value = htmlspecialchars($evaluator[$fieldx]);
                if ($fieldx == 'url') {
                  $url = $evaluator[$fieldx];
                  if (substr($url, 0, 7) != 'http://') {
                    $url = 'http://' . $url;
                  }
                  $value = '<a href="' . $url . '" target="_blank">' . $url . '</a>';
                }
                $values[] = $value;
              }
              echo '<td rowspan="' .  count($downs). '">' . implode('<br/>', $values) . '</td>';
            }
            $first = true;
            foreach ($downs as $down) {
              if (!$first) {
                echo '<tr>';
              }
              foreach (['platform', 'version', 'download', 'installed'] as $field2) {
                echo '<td>' . htmlspecialchars($down[$field2]) . '</td>';
              }
              if (!$first) {
                echo '</tr>';
              }
              $first = false;
            }
            echo '</tr>';
          }
        ?>
      </tbody>
    </table>
  </div>
</div>
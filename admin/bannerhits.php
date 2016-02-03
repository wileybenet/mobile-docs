<?php
if (array_key_exists('truncate', $_REQUEST)) {
  Bannerhit::Truncate();
}
?>
<div class="container white">
  <div class="content left">
    <a href="?truncate">Erase Banner Hits</a>
    <table class="data">
      <thead>
        <th>Date</th>
        <th>IP</th>
        <th>Data1</th>
        <th>Data2</th>
        <th>Data3</th>
      </thead>
      <tbody>
        <?php
          $bannerhits = Bannerhit::read(['*'], FALSE, ['created > CURRENT_DATE - INTERVAL 2 MONTH'], 'created DESC');
          foreach ($bannerhits as $bannerhit) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($bannerhit['created']) . '</td>';
            echo '<td>' . htmlspecialchars($bannerhit['ip']) . '</td>';
            echo '<td>' . htmlspecialchars($bannerhit['data1']) . '</td>';
            echo '<td>' . htmlspecialchars($bannerhit['data2']) . '</td>';
            echo '<td>' . htmlspecialchars($bannerhit['data3']) . '</td>';
            echo '</tr>';
          }
        ?>
      </tbody>
    </table>
  </div>
</div>
<div class="container white">
  <div class="content left">
    <table class="data">
      <thead>
        <th>Date</th>
        <th>IP</th>
        <th>URI</th>
      </thead>
      <tbody>
        <?php
          $adhits = Adhit::read(['*'], FALSE, ['created > CURRENT_DATE - INTERVAL 2 MONTH'], 'created DESC');
          foreach ($adhits as $adhit) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($adhit['created']) . '</td>';
            echo '<td>' . htmlspecialchars($adhit['ip']) . '</td>';
            echo '<td>' . htmlspecialchars($adhit['uri']) . '</td>';
            echo '</tr>';
          }
        ?>
      </tbody>
    </table>
  </div>
</div>
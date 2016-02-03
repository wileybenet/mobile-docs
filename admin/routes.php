<div class="container white">
  <div class="content left">
    <table class="data">
      <thead>
        <th>HTTP Method</th>
        <th>Route</th>
        <th>Controller#Method</th>
      </thead>
      <tbody>
        <?php
          foreach (Router::$routes as $route => $ctrl) {
            $parts = explode('/', $route);
            $r = implode('/', array_slice($parts, 1));
            echo "<tr><td>{$parts[0]}</td><td>/$r</td><td>$ctrl</td></tr>";
          }
        ?>
      </tbody>
    </table>
  </div>
</div>
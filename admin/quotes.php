<div class="container white">
  <div class="content left">
    <table class="data">
      <thead>
        <th>Date</th>
        <th>Expires</th>
        <th>Company</th>
        <th>Contact/Email/Key</th>
        <th>Items</th>
        <th>Views</th>
      </thead>
      <tbody>
        <?php
          $quotes = Quote::read(['*'], FALSE, ['created > CURRENT_DATE - INTERVAL 2 MONTH'], 'created DESC');
          foreach ($quotes as $quote) {
            $expiration = Util2::future_date(substr($quote['created'], 0, 10), 60);
            $items = QuoteItem::read_items($quote['id']);
            $views = QuoteView::read_views($quote['id']);
            $item_details = [];
            foreach ($items as $item) {
              $item_details[] = $item['quantity'] . ' ' . $item['part'];
            }
            $view_details = [];
            foreach ($views as $view) {
              $view_details[] = $view['created'] . ' ' . $view['ip'];
            }
            echo '<tr>';
            echo '<td>' . htmlspecialchars($quote['created']) . '</td>';
            echo '<td>' . htmlspecialchars($expiration) . '</td>';
            echo '<td>' . htmlspecialchars($quote['company']) . '</td>';
            echo '<td>' . htmlspecialchars($quote['technical_name']) . '<br/>';
            echo htmlspecialchars($quote['technical_email']) . '<br/>';
            echo '<a href="/order-form/' . $quote['key'] . '" target="_blank">' . htmlspecialchars($quote['key']) . '</a></td>';
            echo '<td>' . implode('<br/>', $item_details) . '</td>';
            echo '<td>' . implode('<br/>', $view_details) . '</td>';
            echo '</tr>';
          }
        ?>
      </tbody>
    </table>
  </div>
</div>
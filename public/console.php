<?php

  require_once('../../config/config.inc.php');

  $reset = '<b>MD php&gt; </b>';
  $code = '';
  $commands = [];
  $error_occured = FALSE;

  set_error_handler(function($e, $m) use (&$code, &$error_occured) {
    $code .= "<i class=\"error\">$m</i><br />";
    $error_occured = TRUE;
  });

  if (isset($_POST['code'])) {
    $commands = array_merge($commands, json_decode($_POST['commands']));

    foreach ($commands as $cmd) {
      eval("\$value = $cmd;");
    }
    $error_occured = FALSE;

    array_push($commands, $_POST['code']);

    $code = $_POST['log'];
    $code = substr($code, 0, strlen($code));


    if (strlen($_POST['code']) > 0) {
      $code .= $_POST['code'] . "<br />";
      $code .= '<div class="code-block">';

      $value = '';
      @eval("\$value = {$_POST['code']};");
      $error = error_get_last();
      if ($error) {
        $code .= "<i class=\"error\">Error: {$error['message']}</i><br />";
      }
      if (!$error_occured) {
        $code .= '<code>';
        if (is_array($value)) {
          $value = json_encode($value);
        } elseif (is_object($value)) {
          $value = 'function Closure';
        }
        $code .= htmlspecialchars($value);
        $code .= '</code>';
      }
      $code .= "</div>";
    } else {
      $code .= '<br />';
    }
  }

  $code .= $reset;

?>

<!DOCTYPE html>
<html>
<head>
  <title>Console</title>
  <style>
    .container {
      width: 800px;
      margin: 50px auto;
    }

    .console * {
      font-size: 14px;
      font-family: monospace;
    }
    #console-input {
      border: 0;
      width: 90%;
      outline: 0;
    }
    #console-wrapper {
      height: 400px;
      overflow-y: scroll;
      border-bottom: 0px;
      /*padding: 0 6px;*/
    }
    #console-log {
      word-wrap: break-word;
    }
    .code-block {
      /*padding-left: 17px;*/
      margin: 2px 0;
    }
    em {
      color: #AAA;
    }
    .error {
      color: #C00;
    }

  </style>
  <script type="text/javascript" src="/assets/js/lib/jquery.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      var commands = <?php echo json_encode($commands); ?>.reverse(),
        index = -1;
      console.log(commands);
      $('#console-input').focus();
      $('#console-wrapper').scrollTop(9999999);

      $('#console-input').on('keydown', function(evt) {
        if (evt.keyCode === 38) {
          evt.preventDefault();
          $('#console-input').val(commands[++index]);
        } else if (evt.keyCode === 40) {
          evt.preventDefault();
          $('#console-input').val(commands[--index]);
        } else if (evt.keyCode === 76 && evt.ctrlKey) {
          $('#hidden-log').val('<?php echo $reset; ?>');
          $('#console-log').html('<?php echo $reset; ?>');
        }
      });
      $('#console-input').on('blur', function() {
        index = 0;
      });
    });
  </script>
</head>
<body>
  <div class="container light">
    <div class="content expand">
      <form class="console" method="POST" action="console.php">
        <div id="console-wrapper">
          <span id="console-log">
            <?php echo $code; ?>
          </span>
          <input id="console-input" name="code" />
        </div>
        <input value="<?php echo htmlspecialchars(json_encode($commands)); ?>" type="hidden" name="commands" />
        <input id="hidden-log" value="<?php echo htmlspecialchars($code); ?>" type="hidden" name="log" />
      </form>
    </div>
  </div>
</body>
</html>
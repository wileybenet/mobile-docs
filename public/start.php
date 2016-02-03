<?php

  $complete = true; // file_exists('../md_config.php');
  $form = FALSE;

  if (!$complete && $_POST['submitted']) {

    require_once('../cli/cli_tools.php');

    $args = $_POST;

    $request = explode('/', $_SERVER['REQUEST_URI']);
    array_pop($request);
    $dir = implode('/', $request);
    $webpath = realpath(dirname(dirname(__FILE__) . '.up'));
    $parentpath = dirname($webpath);

    render_template('../config/config.inc.php.template', '../../config/config.inc.php', [
      'dir' => $dir,
      'webpath' => $webpath,
      'parentpath' => $parentpath
    ]);

    function create_dir($path) {
      if (!file_exists($path)) {
        mkdir($path, 0777, TRUE);
      }
    }

    create_dir('../../uploads/doc/all');
    create_dir('../../uploads/post');
    create_dir('../../uploads/solution');
    create_dir('../../config');

    write_file('../../config/md_config.json', json_encode($args));

    $title = "Configuration Complete";

  } elseif ($complete) {

    $title = "Configuration Complete";

  } else {
    
    $title = "Configure MobileDocs";
    $form = TRUE;
  }

?>

<!DOCTYPE html>
<html>
  <head>
    <title>MobileDocs | Start</title>
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="assets/css/layout.css">
    <link rel="stylesheet" type="text/css" href="assets/css/main.css">
  </head>
  <body>
    <div id="heading" class="containers">
      <div class="content left">
        <a id="title-wrapper" href="#">
          MobileDocs
        </a>
      </div>
    </div>
    <div id="content-wrapper">
      <div id="heading-spacer"></div>
      <div class="container light">
        <div class="content expand">
          <h2><?php echo $title; ?></h2>
<?php if ($form) { ?>
          <form class="form-table" action="start.php" method="POST">
            <table>
              <tr>
                <td>App Name</td>
                <td><input type="text" name="app_name" placeholder="" /></td>
              </tr>
              <tr>
                <td colspan="2" style="text-align: center"><input name="submitted" type="hidden" value="true" />
                <button type="submit" class="btn">Start Application</button></td>
              </tr>
            </p>
          </form>
<?php } ?>
        </div>
      </div>
    </div>
  </body>
</html>
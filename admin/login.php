<!DOCTYPE html>
<html>
  <head>
    <title>404: Not Found</title>
    <link rel="stylesheet" type="text/css" href="/assets/css/layout.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/main.css">
  </head>
  <body>
    <div id="heading">
      <div class="content left">
        <a id="title-wrapper" href="#">
          MobileDocs
        </a>
      </div>
    </div>
    <div id="content-wrapper">
      <div id="heading-spacer"></div>
    </div>
    <div class="container light">
      <div class="content expand">
        <h1>
          Administrator Login
        </h1>
        <form action="login" method="POST">
          Username
          <input type="text" name="username" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" />
          Password
          <input type="password" name="password" />
          <button type="submit" class="btn">Log In</button>
        </form>
        <p class="err">
          <?php echo Session::$error; ?>
        </p>
      </div>
    </div>
  </body>
</html>    
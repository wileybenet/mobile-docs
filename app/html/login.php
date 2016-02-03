<div class="container light">
  <div class="content expand">
    <h2>User Login</h2>
    <form action="/login" method="POST">
      Email
      <input type="text" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" />
      Password
      <input type="password" name="password" />
      <button type="submit" class="btn">Log In</button>
    </form>
    <!--
    <p>
      <a href="/support?username">Forgot Username?</a>
      <a href="/support?password">Forgot Password?</a>
    </p>
    -->
    <p class="err">
      <?php echo Session::$error; ?>
    </p>
  </div>
</div>
<?php
// Render the header
$this->render('backend/header.html');
?>


      <div class="section header_section">
        <div class="container narrow_container">
          <h1><?php echo $Html->link('PeanutCMS', array()); ?></h1>
        </div>
      </div>

      <form action="<?php echo $this->link(); ?>" method="post">

      <div class="top_shadow"></div>
      <div class="section dark_section">
        <div class="container narrow_container">
          <p>
            <label for="login_username" class="small">Username</label>
            <input type="text" name="login_username" class="text bigtext"
              id="login_username" <?php if (isset($loginUsername)) {
                echo 'value="' . $loginUsername . '"';
              }
              ?> />
          </p>
          <p>
            <label for="login_password" class="small">Password</label>
            <input type="password" name="login_password" class="text bigtext"
              id="login_password" />
          </p>
        </div>
      </div>
      <div class="bottom_shadow"></div>

      <div class="section">
        <div class="container narrow_container">
          <div class="aright">
            <input type="submit" class="button" value="Reset password" />
            <input type="submit" class="button publish" value="Log in" />
          </div>
        </div>
      </div>

      </form>

<?php
$this->render('backend/footer.html');
?>


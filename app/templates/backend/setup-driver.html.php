<?php
/*
 * Template for "404 not found"
 */

// Render the header
$this->renderTemplate('backend/header.html');
?>

    <form action="<?php echo $saveAction; ?>" method="post">

      <div class="section">
        <div class="container">
          <h1>Welcome to PeanutCMS</h1>
          <p>
          	You have selected the <strong><?php echo $driver['name']; ?></strong> database driver.
          	The following information is required.
          </p>
          <p>
            <label class="small" for="server">MySQL server</label>
            <input type="text" class="text" name="server" id="server" />
            <span class="description">The server on which MySQL is running</span>
          </p>
          <p>
            <label class="small" for="username">MySQL username</label>
            <input type="text" class="text" name="username" id="username" />
            <span class="description">The username used to access the MySQL server</span>
          </p>
          <p>
            <label class="small" for="password">MySQL password</label>
            <input type="password" class="text" name="password" id="password" />
            <span class="description">The password used to access the MySQL server, can be empty</span>
          </p>
          <p>
            <label class="small" for="database">MySQL database</label>
            <input type="text" class="text" name="database" id="database" />
            <span class="description">The database to install PeanutCMS in</span>
          </p>
          <p>
            <label class="small" for="tablePrefix">Table prefix</label>
            <input type="text" class="text" name="tablePrefix" id="tablePrefix" />
            <span class="description">Optional. Can be used to prevent conflict with other tables in the database</span>
          </p>
        </div>
      </div>

      <div class="section">
        <div class="container">
          <div class="aright">
            <a href="<?php echo $cancelAction; ?>" class="button">Cancel</a>
            <input type="submit" class="button publish" value="Save" />
          </div>
        </div>
      </div>

      </form>

<?php
$this->renderTemplate('backend/footer.html');
?>


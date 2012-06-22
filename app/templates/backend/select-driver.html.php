<?php
/*
 * Template for "404 not found"
 */

// Render the header
$this->render('backend/header.html');
?>


      <div class="section">
        <div class="container">
          <h1>Welcome to PeanutCMS</h1>
          <p>Please select your desired database driver.
            SQLite is recomended if available, since it requires
            almost no setup.
          </p>
        </div>
        <div class="container">
<?php
$i = 0;
$count = count($drivers);
foreach ($drivers as $driver) {
  echo '<div class="db_driver"><h2>' . $driver['name'] . '</h2><p>';
  if ($driver['isAvailable']) {
    echo 'Available.</p><div class="button_container"><a href="' . $driver['link'] . '" class="button">Select ' . $driver['name'] . '</a></div>';
  }
  else {
    echo trl('Unavailable. Missing the "%l" PHP extension.</p>', 'Unavailable. Missing the "%l" PHP extensions.</p>',
             '", "', '" and "', $driver['missingExtensions']);
  }
  echo '</div><div class="clearl"></div>';
  if (++$i < $count)
    echo '<div class="separator"></div>';
}
?>
        </div>
      </div>

<?php
$this->render('backend/footer.html');
?>


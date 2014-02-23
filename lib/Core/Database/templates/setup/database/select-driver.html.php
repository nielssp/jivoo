<?php
$this->extend('setup/layout.html');
?>

      <?php echo $Form->begin(); ?>

      <div class="section">
        <div class="container">
          <h1><?php echo tr('Welcome to %1', $app['name']); ?></h1>
          <p><?php echo tr('Please select your desired database driver.'); ?>
          </p>
        </div>
        <div class="container">
<?php
$first = true;
foreach ($drivers as $driver) :
?>

<?php
  if ($first) {
    $first = false;
  }
  else {
    echo '<div class="separator"></div>';
  }
?>
          <div class="db_driver">
            <h2><?php echo $driver['name']; ?></h2>
            <p>
              <?php if ($driver['isAvailable']) : ?>
              <?php echo tr('Available'); ?>
            </p>
            <div class="button_container">
              <?php echo $Form->submit(tr('Select %1', $driver['name']),
        $driver['driver']); ?>
            </div>
              <?php 
  else : ?>
<?php
    echo tn(
      'Unavailable. Missing the "%1{", "}{" and "}" PHP extensions',
      'Unavailable. Missing the "%1{", "}{" and "}" PHP extension',
      $driver['missingExtensions']
    );
?>
            </p>
            <?php endif; ?>
          </div> 
          <div class="clearl"></div>
<?php endforeach; ?>
        </div>
      </div>

      <?php echo $Form->end(); ?>

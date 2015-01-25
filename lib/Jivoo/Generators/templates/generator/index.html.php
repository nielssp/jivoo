<?php
$this->extend('generator/layout.html');
?>
<p><?php echo tr('Welcome to the Jivoo web application framework.')?></p>

<p><?php echo tr(
  'No valid application configuration was found in the specified application directory (%1).',
  $appDir
); ?></p>

<p><?php echo tr('Do you want Jivoo to generate a new application?')?></p>

<input type="submit" value="<?php echo tr('Continue'); ?>" />
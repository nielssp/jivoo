<?php 
$this->import(
  'jquery.js', 'jquery-ui.js', 'js.cookie.js',
  'jivoo/console/console.js', 'jivoo/console/tools.css'
);
?>

<div id="jivoo-dev-tools">
  <div class="jivoo-devbar">
    <div class="jivoo-devbar-handle"><?php echo tr('Development'); ?></div>
    <ul class="jivoo-devbar-tools">
    </ul>
    <ul class="jivoo-devbar-settings">
      <li>
        <label class="jivoo-devbar-autohide"><input type="checkbox" class="jivoo-devbar-fade"> <?php echo tr('Fade'); ?></label>
      </li>
      <li>
        <label class="jivoo-devbar-autohide"><input type="checkbox" class="jivoo-devbar-hide"> <?php echo tr('Hide'); ?></label>
      </li>
    </ul>
  </div>
  <div class="jivoo-dev-frame-container">
    <div class="jivoo-dev-frame">
      <div class="jivoo-dev-frame-content">
      </div>
    </div>
  </div>
</div>
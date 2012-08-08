<?php
$this->setIndent(4);
$this->insertScript('backend-js', $this->file('js/backend.js'), array('jquery-ui', 'jquery-hotkeys'));
$this->insertStyle('backend-css', $this->file('css/backend.css'));
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $title; ?> | PeanutCMS</title>

<?php $this->output('head-meta'); ?>

<?php $this->output('head-styles'); ?>

<?php $this->output('head-scripts'); ?>

  </head>
  <body>
<?php $this->output('body-top'); ?>

<?php if (!isset($noHeader) OR !$noHeader): ?>
    <div id="header">
      <div id="bar">
<?php if (isset($menu)): ?>
        <ul class="menubar">
<?php foreach ($menu as $key => $category): ?>
          <li class="menu">
            <div class="header item">
              <a href="#" data-shortcut-on="root">
                <?php echo $category->label; ?>
              </a>
            </div>
            <ul class="items">
<?php $prevGroup = NULL; ?>
<?php foreach ($category as $link): ?>

<?php if (isset($prevGroup) AND $prevGroup != $link->group): ?>
              <li class="separator"></li>
<?php endif; ?>
              <li class="item">
                <a href="<?php echo $this->link($link); ?>" data-shortcut-on="<?php echo $key; ?>">
                  <?php echo $link->label; ?>
                </a>
              </li>
<?php $prevGroup = $link->group; ?>
<?php endforeach; ?>
            </ul>
          </li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
        <div class="right">
          <?php echo $Html->link($site['title']); ?>
        </div>
      </div>
      <div id="shadow"></div>
    </div>
<?php endif; ?>

    <div id="content">
  
<?php foreach (LocalNotification::all() as $notification): ?>

<div class="section">
  <div class="container notification notification-<?php echo $notification->type; ?>">
    <strong>
      <?php echo $notification->label; ?>
    </strong>
    <?php  echo $notification->message; ?>
  </div>
</div>

<?php $notification->delete(); ?>

<?php endforeach; ?>

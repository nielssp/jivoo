<?php
$this->setHtmlIndent(4);
$this->insertScript('backend-js', $this->getFile('js/backend.js'), array('jquery-ui', 'jquery-hotkeys'));
$this->insertStyle('backend-css', $this->getFile('css/backend.css'));
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $title; ?> | PeanutCMS</title>

<?php $this->outputHtml('head-meta'); ?>

<?php $this->outputHtml('head-styles'); ?>

<?php $this->outputHtml('head-scripts'); ?>

  </head>
  <body>
<?php $this->outputHtml('body-top'); ?>

<?php if (!isset($noHeader) OR !$noHeader): ?>
    <div id="header">
      <div id="bar">
<?php if (isset($menu)): ?>
        <ul class="menubar">
<?php foreach ($menu as $category): ?>
          <li class="menu">
            <div class="header item">
              <a href="#"
                data-shortcut="<?php echo $category->shortcut; ?>">
                <?php echo $category->title; ?>
              </a>
            </div>
            <ul class="items">
<?php $prevGroup = NULL; ?>
<?php foreach ($category->links as $link): ?>

<?php if (isset($prevGroup) AND $prevGroup != $link->group): ?>
              <li class="separator"></li>
<?php endif; ?>
              <li class="item">
                <a href="<?php echo $link->getLink(); ?>"
                  data-shortcut="<?php echo $link->shortcut; ?>">
                  <?php echo $link->title; ?>
                </a>
              </li>
<?php $prevGroup = $link->group; ?>
<?php endforeach; ?>
            </ul>
          </li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
        <div class="right">PeanutCMS</div>
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

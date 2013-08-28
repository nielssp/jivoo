<?php
$this->script(array(
  'jquery-ui.js', 'jquery-hotkeys.js', 'backend.js'
));
$this->style('backend.css');
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $title; ?> | PeanutCMS</title>

<?php echo $this->block('meta'); ?>
<?php echo $this->block('style'); ?>
<?php echo $this->block('script'); ?>

  </head>
  <body>
<?php echo $this->block('body-top'); ?>

<?php if (!isset($noHeader) OR !$noHeader) : ?>
    <div id="header">
      <div id="bar">
<?php if (isset($menu)) : ?>
        <ul class="menubar">
<?php foreach ($menu as $key => $category) : ?>
          <li class="menu">
            <div class="header item">
              <a href="" data-shortcut-on="root">
                <?php echo $category->label; ?>
              </a>
            </div>
            <ul class="items">
<?php $prevGroup = null; ?>
<?php foreach ($category as $link) : ?>

<?php if (isset($prevGroup) AND $prevGroup != $link->group) : ?>
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
        <div class="right" id="header-title">
          <?php echo $Html->link($site['title']); ?>
        </div>
      </div>
      <div id="shadow"></div>
    </div>
<?php endif; ?>

    <div id="content">
  
<?php foreach ($messages as $flash) : ?>

<div class="section">
  <div class="container notification notification-<?php echo $flash->type; ?>">
    <strong>
      <?php echo $flash->label; ?>
    </strong>
    <?php echo $flash->message; ?>
  </div>
</div>
<?php $flash->delete(); ?>

<?php endforeach; ?>

<?php echo $this->block('content'); ?>


    </div>
<?php if (isset($aboutLink)) : ?>
    <div class="footer" id="poweredby">
      Powered by
      <a href="<?php echo $aboutLink; ?>">
        PeanutCMS
<?php echo $version ?>
      </a>
    </div>
<?php endif; ?>

    <div class="footer" id="links">
      <a href="#">Help</a>
    </div>
<?php echo $this->block('body-bottom'); ?>

  </body>
</html>


<?php
$this->script(array(
  'jquery-ui.js', 'jquery-hotkeys.js'
));
$this->style('lookout.css');
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

  <div id="header">
    <?php echo $Html->link($site['title']); ?>
    <div id="add-content">
      <?php if (isset($menu) AND isset($menu['add'])): ?>
      <ul>
        <?php foreach ($menu['add'] as $link): ?>
        <li class="item">
          <a href="<?php echo $this->link($link); ?>"
            <?php if ($this->isCurrent($link)) echo 'class="selected"'; ?>
            data-shortcut-on="<?php echo $key; ?>">
            <?php echo $link->label; ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul> 
      <?php endif; ?>
    </div>
    <div id="account">
    </div>
  </div>
  <div id="main">
   <div id="menu">
<?php if (isset($menu)) : ?>
<?php foreach ($menu as $key => $category) : ?>
<?php if ($key == 'add') continue; ?>
        <div class="submenu">
          <div class="submenu-title">
            <?php echo $category->label; ?>
          </div>
          <ul>
<?php $prevGroup = null; ?>
<?php foreach ($category as $link) : ?>

<?php if (isset($prevGroup) AND $prevGroup != $link->group) : ?>
            <li class="separator"></li>
<?php endif; ?>
            <li class="item">
              <a href="<?php echo $this->link($link); ?>"
                <?php if ($this->isCurrent($link)) echo 'class="selected"'; ?>
                data-shortcut-on="<?php echo $key; ?>">
                <?php echo $link->label; ?>
              </a>
            </li>
<?php $prevGroup = $link->group; ?>
<?php endforeach; ?>
          </ul>
        </div>
<?php endforeach; ?>
<?php endif; ?>
      </div>
      <div id="content">

<?php if (!$this->isEmpty('records')): ?>
        <div class="record-list">
<?php echo $this->block('records'); ?>
        </div>
<?php endif; ?>

        <div class="content-right">
<?php echo $this->block('content'); ?>
        </div>

        <div id="footer">
          Powered by
          <?php echo $Html->link('PeanutCMS ' . $app['version'], 'Backend::about'); ?>
          <a href="#">Help</a>
        </div>
  
      </div>
    </div>

<?php echo $this->block('body-bottom'); ?>

  </body>
</html>


<?php $this->setHtmlIndent(4); ?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $title; ?> | PeanutCMS</title>

<?php $this->outputHtml('head-top'); ?>

    <link rel="stylesheet" type="text/css" href="<?php echo w(PUB . 'css/arachis/jquery-ui-1.8.17.custom.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo $this->getFile('css/backend.css'); ?>" />

    <script src="<?php echo w(PUB . 'js/jquery-1.7.1.min.js'); ?>" type="text/javascript"></script>
    <script src="<?php echo w(PUB . 'js/jquery-ui-1.8.17.custom.min.js'); ?>" type="text/javascript"></script>
    <script src="<?php echo w(PUB . 'js/jquery.hotkeys-0.7.9.min.js'); ?>" type="text/javascript"></script>

    <script src="<?php echo $this->getFile('js/backend.js'); ?>" type="text/javascript"></script>

<?php $this->outputHtml('head-bottom'); ?>

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

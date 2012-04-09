<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $title; ?></title>

<?php $this->outputHtml('head-top'); ?>

    <link rel="stylesheet" type="text/css" href="<?php echo w(PUB . 'css/arachis/jquery-ui-1.8.17.custom.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo w(PUB . 'css/backend.css'); ?>" />

    <script src="<?php echo w(PUB . 'js/jquery-1.7.1.min.js'); ?>" type="text/javascript"></script>
    <script src="<?php echo w(PUB . 'js/jquery-ui-1.8.17.custom.min.js'); ?>" type="text/javascript"></script>
    <script src="<?php echo w(PUB . 'js/tinymce/jquery.tinymce.js'); ?>" type="text/javascript"></script>
    <script src="<?php echo w(PUB . 'js/jquery.hotkeys-0.7.9.min.js'); ?>" type="text/javascript"></script>

    <script src="<?php echo w(PUB . 'js/backend.js'); ?>" type="text/javascript"></script>

<?php $this->outputHtml('head-bottom'); ?>

  </head>
  <body>

<?php $this->outputHtml('body-top'); ?>

    <div id="header">
      <div id="bar">
        <div class="right">PeanutCMS</div>
      </div>
      <div id="shadow"></div>
    </div>

    <div id="content">

    <?php
    if ($backendMenu) {
      echo 'to';
    }
    ?>
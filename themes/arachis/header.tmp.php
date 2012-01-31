<!DOCTYPE html>
<html>
  <head>
    <?php $PEANUT['theme']->outputHtml('head-top'); ?>
    
    <title><?php echo $PEANUT['functions']->call('themeTitle'); ?></title>

    <link rel="stylesheet" type="text/css" href="<?php echo $PEANUT['theme']->getFile('style.css'); ?>" />

    <?php $PEANUT['theme']->outputHtml('head-bottom'); ?> 
  </head>
  <body>
    <?php $PEANUT['theme']->outputHtml('body-top'); ?>

    <div id="main">

    <div id="header">
      <div id="title">
        <h1><a href="<?php echo WEBPATH; ?>"><?php echo $PEANUT['configuration']->get('title'); ?></a></h1>
        <h2><?php echo $PEANUT['configuration']->get('subtitle');; ?></h2>
      </div>
      <?php
      $rand = rand(1, 5);
      ?>
      <img src="<?php echo $PEANUT['theme']->getFile('banner' . $rand . '.jpg'); ?>"
           alt="<?php echo $PEANUT['configuration']->get('title'); ?>" style="width:950px;height:200px;" />
      <div id="navigation">
        <ul>
          <?php
          while ($menuItem = $PEANUT['theme']->listMenu()):
            ?>
            <li<?php if ($menuItem['selected']) echo ' class="selected"'; ?>>
              <a href="<?php echo $menuItem['link']; ?>"><?php echo $menuItem['label']; ?></a>
            </li>
            <?php
          endwhile;
          ?>
        </ul>
      </div>
    </div>

    <div id="content">
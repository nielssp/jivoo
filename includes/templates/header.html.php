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
    
    <h1><?php echo $PEANUT['configuration']->get('title'); ?></h1>
    <p><em><?php echo $PEANUT['configuration']->get('subtitle'); ?></em></p>

    <a href="<?php echo WEBPATH; ?>">Index</a>
    <a href="<?php echo $PEANUT['http']->getLink($PEANUT['templates']->getPath('post', array('p' => '20110826085800'))); ?>">
    <?php echo $PEANUT['templates']->getTitle('post', array('p' => '20110826085800')); ?></a>
    <a href="<?php echo WEBPATH; ?>about">About</a>
    <a href="<?php echo WEBPATH; ?>stuff/and/links">Links</a>
    <?php if ($PEANUT['user']->loggedIn): ?>
    <a href="<?php echo $PEANUT['actions']->add('logout'); ?>">Log out</a>
    <?php else: ?>
    <a href="<?php echo WEBPATH; ?>admin">Log in</a>
    <?php endif; ?>

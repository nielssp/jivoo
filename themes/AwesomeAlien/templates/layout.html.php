<?php
$this->script('jquery.js');
$this->style('theme.css');
?>
<!DOCTYPE html>
<html>
  <head>
    <title>
<?php
if (isset($title)) {
  echo $title . ' | ' . $site['title'];
}
else {
  echo $site['title'] . ' | ' . $site['subtitle'];
}
?>
   </title>

<?php echo $this->block('meta'); ?>
<?php echo $this->block('style'); ?>
<?php echo $this->block('script'); ?>
  </head>
  <body>
<?php echo $this->block('body-top'); ?>

    <div id="main">

      <header>
        <div class="title">
          <h1><?php echo $Html->link($site['title']); ?></h1>
          <h2><?php echo $site['subtitle']; ?></h2>
        </div>
        <?php
  $rand = floor(time() / 60) % 5 + 1;
        ?>
        <img src="<?php echo $this->file('img/banner.jpg'); ?>"
             alt="<?php echo $site['title']; ?>" />
        <nav>
          <ul>
<?php foreach ($Menu->getMenu('main') as $link) : ?>
            <li>
              <?php echo $Html->link(h($link->title), $link); ?>
            </li>
<?php endforeach; ?>
          </ul>
        </nav>
      </header>

      <div id="content">
      
      <?php echo $this->block('content'); ?>
  
      </div>
  
      <aside>
<?php foreach ($Widgets->get('sidebar') as $widget): ?>
        <div class="widget">
          <div class="widget-title"><?php echo $widget['title']; ?></div>
          <div class="widget-content"><?php echo $widget['content']; ?></div>
        </div>
<?php endforeach; ?>
      
<?php echo $this->block('sidebar'); ?>
      </aside>

      <footer>
        <h1><?php echo $Html->link($site['title']); ?></h1>
        <div class="powered-by">
          <?php echo $Html->link('Powered by Jivoo.', 'http://apakoh.dk'); ?>
        </div>
      </footer>
    </div>

    
<?php echo $this->block('body-bottom'); ?>
  </body>
</html>
    

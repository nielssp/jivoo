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

    <script type="text/javascript">
      $(document).ready(function() {
        // jQuery
      });
    </script>
  </head>
  <body>
<?php echo $this->block('body-top'); ?>

    <div id="main">

    <div id="header">
      <div id="title">
        <h1><?php echo $Html->link($site['title']); ?></h1>
        <h2><?php echo $site['subtitle']; ?></h2>
      </div>
      <?php
$rand = floor(time() / 60) % 5 + 1;
      ?>
      <img src="<?php echo $this->file('banner' . $rand . '.jpg'); ?>"
           alt="<?php echo $site['title']; ?>" style="width:950px;height:200px;" />
      <div id="navigation">
        <ul>
<?php foreach ($Menu->getMenu('main') as $link) : ?>
          <li<?php if ($this->isCurrent($link)) echo ' class="selected"'; ?>>
            <?php echo $Html->link(h($link->title), $link); ?>
          </li>
<?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div id="content">
    
    <?php echo $this->block('content'); ?>

    </div>

<div id="sidebar">
<?php echo $this->block('sidebar'); ?>
</div>

<div class="clear"></div>

<div id="footer">
  <h2>
    <?php echo $Html->link($site['title']); ?></h2>
  <div id="powered-by">
    <?php echo $Html->link('Powered by Apakoh Core.', 'http://apakoh.dk'); ?>
  </div>
</div>
</div>

    
<?php echo $this->block('body-bottom'); ?>
  </body>
</html>
    
<?php
$this->setindent(4);
$this->requestScript('jquery');
$this->insertStyle('theme-style', $this->file('style.css'));
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
<?php $this->output('head-meta'); ?>

<?php $this->output('head-styles'); ?>

<?php $this->output('head-scripts'); ?>

    <script type="text/javascript">
      $(document).ready(function() {
        // jQuery
      });
    </script>
  </head>
  <body>
<?php $this->output('body-top'); ?>

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
<?php foreach (Link::getMenu('main') as $link) : ?>
          <li<?php if ($this->isCurrent($link)) echo ' class="selected"'; ?>>
            <?php echo $Html->link(h($link->title), $link); ?>
          </li>
<?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div id="content">

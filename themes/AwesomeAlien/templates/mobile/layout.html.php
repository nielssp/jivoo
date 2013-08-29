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


<?php echo $this->block('content'); ?>

<p><?php echo $Html->link('Powered by Apakoh Core.', 'http://apakoh.dk'); ?></p>

    
<?php echo $this->block('body-bottom'); ?>
  </body>
</html>
    
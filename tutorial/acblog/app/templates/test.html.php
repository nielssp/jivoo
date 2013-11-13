<?php
$this->extend('layout.html');
?>

  <p>method: <?php echo $method; ?></p>

  <form action="<?php echo $this->link(array()); ?>" method="post">
    <input type="hidden" name="method" value="patch" />
    <input type="submit" value="Submit" />
  </form>

<?php
echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>

<rss version="2.0">

<channel>
  <title><?php echo $site['title']; ?></title>
  <link><?php echo $this->link(); ?></link>
  
  <?php echo $this->block('content'); ?>
</channel>

</rss>
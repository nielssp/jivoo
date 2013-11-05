<?php
echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">

<channel>
  <title><?php echo $site['title']; ?></title>
  <atom:link href="<?php echo $this->url(array()); ?>" rel="self" type="application/rss+xml" />
  <link><?php echo $this->url(); ?></link>
  <description><?php echo $site['subtitle']; ?></description>
<?php if (isset($lastBuildDate)): ?>
  <lastBuildDate><?php echo date('r', $lastBuildDate); ?></lastBuildDate>
<?php endif; ?>
  
  <?php echo $this->block('content'); ?>
</channel>

</rss>
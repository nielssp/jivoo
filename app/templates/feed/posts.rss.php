<?php $this->extend('feed/layout.rss'); ?>

<?php foreach ($posts as $post): ?>

<item>
  <title><?php echo h($post->title); ?></title>
  <description><![CDATA[<?php echo $post->content; ?>]]></description>
  <link><?php echo $this->link($post); ?></link>
  <pubDate><?php echo date('r', $post->date); ?></pubDate>
</item>

<?php endforeach; ?>
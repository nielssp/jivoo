<?php $this->extend('layout.html'); ?>

<?php foreach ($posts as $post) : ?>

<h2>
  <?php echo $Html->link(h($post->title), $post); ?>
</h2>

<p>
  Published <?php echo fdate($post->createdAt); ?>
@ <?php echo ftime($post->createdAt); ?>
</p>
<?php echo $post->content; ?>

<?php endforeach; ?>

<p>
  <?php if (!$Pagination->isLast())
  echo $Html->link('&#8592; Older', $Pagination->nextLink()); ?>
  Page <?php echo $Pagination->getPage(); ?>
  of <?php echo $Pagination->getPages(); ?>
  <?php if (!$Pagination->isFirst())
  echo $Html->link('Newer &#8594;', $Pagination->prevLink()); ?>
</p>


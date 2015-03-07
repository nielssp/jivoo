<ul class="comments">
<?php foreach ($comments as $comment): ?>

<li>
<div class="comment">
<div class="author"><?php echo h($comment->author); ?></div>
<div class="content">
<?php echo h($comment->content); ?>
</div>
<div class="byline">
<?php
echo '<time datetime="' . date('c', $comment->created)
      . '" title="' . ldate($comment->created) . '">'
      . sdate($comment->created) . '</time>';
?>
</div>

</div>
</li>

<?php endforeach; ?>
</ul>

<?php echo $this->embed('posts/pagination.html'); ?>

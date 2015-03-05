<?php foreach ($comments as $comment): ?>

<p>
<strong><?php echo h($comment->author); ?>:</strong>
<?php echo h($comment->content); ?>
</p>

<?php endforeach; ?>
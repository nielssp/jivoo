<ul>
<?php foreach ($comments as $comment): ?>
<?php $post = $comment->getPost(); ?>
<li><?php echo $Html->link('Re: ' . $post->title, $comment); ?></li>
<?php endforeach; ?>
</ul>
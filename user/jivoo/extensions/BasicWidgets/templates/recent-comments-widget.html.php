<ul>
<?php foreach ($comments as $comment): ?>
<li><?php echo $Html->link('Re: ' . $comment->post->title, $comment); ?></li>
<?php endforeach; ?>
</ul>
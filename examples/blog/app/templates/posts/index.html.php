<?php foreach ($posts as $post): ?>

<h1><?php echo $Html->link(h($post->title), $post); ?></h1>

<?php echo $post->content; ?>

<?php endforeach; ?>
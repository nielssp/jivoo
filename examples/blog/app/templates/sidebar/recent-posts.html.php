<ul class="menu">
<?php foreach ($posts as $post): ?>
<li><?php echo $Html->link($post->title, $post); ?></li>
<?php endforeach; ?>
</ul>


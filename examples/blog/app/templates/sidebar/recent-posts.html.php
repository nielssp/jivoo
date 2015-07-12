<ul class="menu">
<?php foreach ($posts as $post): ?>
<li><?php echo $Html->link(h($post->title), $post); ?></li>
<?php endforeach; ?>
</ul>


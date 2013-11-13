<?php
// app/templates/posts/index.html.php
$this->extend('layout.html');
?>
<h1>Posts</h1>
<div class="list-group">
<?php foreach ($posts as $post): ?>

<a class="list-group-item" href="<?php echo $this->link(array(
  'action' => 'view',
  'parameters' => array($post->id)
)); ?>">
<span class="badge"><?php echo fdate($post->created_at); ?></span>
<?php echo $post->title; ?>
</a>

<?php endforeach; ?>
</ul>

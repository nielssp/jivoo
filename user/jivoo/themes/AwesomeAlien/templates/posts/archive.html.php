<?php $this->extend('layout.html'); ?>

<div class="archive-pagination">
<div class="quantity">
<strong>
<?php echo $Pagination->getFrom() . '&ndash;' . $Pagination->getTo(); ?>    
</strong>
of
<strong class="item-count"><?php echo $Pagination->getCount(); ?></strong>
</div>
<div class="title"><?php
switch ($searchType) {
  case 'query':
    echo tr('Search results for: %1', h($query));
    break;
  case 'tag':
    echo tr('Posts tagged with: %1', h($tag->tag));
    break;
  case 'year':
    echo tr('Archive for %1', tdate('Y', $start));
    break;
  case 'month':
    echo tr('Archive for %1', tdate('F Y', $start));
    break;
  case 'day':
    echo tr('Archive for %1', fdate($start));
    break;
  default:
    echo tr('Archive');
    break;
}
?></div>
<?php echo $this->embed('posts/pagination.html'); ?>
</div>

<?php foreach ($posts as $post) : ?>

<div class="post">
  <h1>
    <?php echo $Html->link(h($post->title), $post); ?>
  </h1>
<?php echo $Format->html($post, 'content'); ?>

<div class="byline">
<?php 
if (isset($post->user))
  echo h($post->user->username) . ' | ';
?>
<?php 
echo '<time datetime="' . date('c', $post->created)
      . '" title="' . ldate($post->created) . '">'
      . sdate($post->created) . '</time>';
?> | 
<?php
$comments = $post->comments->where('status = %CommentStatus', 'approved')->count();
if ($comments == 0) {
  echo $Html->link(
    tr('Leave a comment'),
    $this->mergeRoutes($post, array('fragment' => 'comment'))
  );
}
else {
  echo $Html->link(
    tn('%1 comments', '%1 comment', $comments),
    $this->mergeRoutes($post, array('fragment' => 'comments'))
  );
}
?>
</div>


</div>
<?php endforeach; ?>

<?php echo $this->embed('posts/pagination.html'); ?>

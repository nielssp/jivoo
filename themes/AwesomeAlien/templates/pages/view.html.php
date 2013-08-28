<?php $this->extend('layout.html'); ?>

<h1><?php echo h($page->title); ?></h1>
<?php if ($page->state == 'draft') : ?>
<p><strong>This page is a draft and is not visible to the pulic.</strong></p>
<?php endif; ?>

<?php echo $page->content; ?>

<p>
  <?php echo $Html->link('Permalink', $page); ?> |
<?php echo $Html->link('Edit',
    array('action' => 'edit', 'parameters' => array($page->id))); ?>
</p>

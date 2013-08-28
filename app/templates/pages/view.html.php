<?php $this->extend('layout.html'); ?>

<h2>
<?php echo $page->title; ?>
</h2>
<?php echo $page->content; ?>

<p>
<?php echo $Html->link('Permalink', $page); ?> |
<?php echo $Html->link('Edit',
    array('action' => 'edit', 'parameters' => array($page->id))); ?>
</p>

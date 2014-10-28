<?php $this->extend('admin/layout.html'); ?>

<p>Temporary sidebar editor.</p>

<?php echo $Form->formFor($sidebar); ?>

<div class="publish">

<div class="article">

<?php echo $Form->textarea('sidebar', array(
  'placeholder' => tr('Sidebar'),
  'class' => 'content',
)); ?>

<?php echo $Form->submit(tr('Save')); ?>

</div>
</div>

<?php echo $Form->end(); ?>
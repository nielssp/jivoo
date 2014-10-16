<div id="comment">

<h2><?php echo tr('Leave a comment'); ?></h2>
<?php if (!isset($newComment)) : ?>

<p><?php echo tr('Please log in to leave a comment.'); ?></p>
<?php else : ?>

<?php echo $Form->formFor($newComment, array('fragment' => 'comment', 'mergeQuery' => true)); ?>

<?php echo $Form->hidden('parentId'); ?>

<?php if ($user) : ?>

<div class="field">
<label>
<?php echo tr('Logged in as %1.', h($user->username)) ?>
</label>
(<?php echo $Html->link(tr('Log out?'), 'Admin::logout') ?>)
</div>
<?php else : ?>

<div class="field">
<?php echo $Form->label('author'); ?>
<?php echo $Form->ifRequired('author', '<span class="star">*</span>'); ?>
<?php echo $Form->text('author'); ?>
<?php echo $Form->error('author'); ?>
</div>

<div class="field">
<?php echo $Form->label('email'); ?>
<?php echo $Form->ifRequired('email', '<span class="star">*</span>'); ?>
<?php echo $Form->text('email'); ?>
<?php echo $Form->error('email'); ?>
</div>

<div class="field">
<?php echo $Form->label('website'); ?>
<?php echo $Form->ifRequired('website', '<span class="star">*</span>'); ?>
<?php echo $Form->text('website'); ?>
<?php echo $Form->error('website'); ?>
</div>
<?php endif; ?>

<?php foreach ($this->extensions('before-content', 'IFormViewExtension') as $e): ?>
<div class="field">
<?php echo $e->label(); ?>
<?php echo $e->ifRequired('<span class="star">*</span>'); ?>
<?php echo $e->field(); ?>
<?php echo $e->error(); ?>
</div>
<?php endforeach; ?>

<div class="field">
<?php echo $Form->label('content'); ?>
<?php echo $Form->ifRequired('content', '<span class="star">*</span>'); ?>
<?php echo $Editor->get('content'); ?>
<?php echo $Form->error('content'); ?>
</div>

<?php foreach ($this->extensions('after-content', 'IFormExtension') as $e): ?>
<div class="field">
<?php echo $e->label(); ?>
<?php echo $e->ifRequired('<span class="star">*</span>'); ?>
<?php echo $e->field(); ?>
<?php echo $e->error(); ?>
</div>
<?php endforeach; ?>

<p><?php echo $Form->submit(tr('Post comment')); ?>
<?php echo $Form->submit(tr('Cancel'), array('name' => 'cancel')); ?>
</p>
<?php echo $Form->end(); ?>

</div>

<?php endif; ?>
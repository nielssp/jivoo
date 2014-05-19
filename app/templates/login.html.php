<?php $this->extend('layout.html'); ?>

<h1>Login test</h1>

<?php foreach ($this->messages as $message): ?>

<p><?php echo $message->message; $message->delete(); ?></p>

<?php endforeach; ?>

<?php echo $Form->form(); ?>

<?php echo $Form->text('username'); ?>
<?php echo $Form->password('password'); ?>

<?php echo $Form->submit(); ?>

<?php echo $Form->end(); ?>

<?php if (isset($this->user)): ?>

<p>Logged in as: <?php echo $this->user->username; ?></p>

<?php endif; ?>
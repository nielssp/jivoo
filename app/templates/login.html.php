<?php $this->extend('layout.html'); ?>

<h1>Login test</h1>

<?php foreach ($this->messages as $message): ?>

<p><?php echo $message->message; $message->delete(); ?></p>

<?php endforeach; ?>

<?php echo $Form->form(); ?>

<?php echo $Form->text('username'); ?>
<?php echo $Form->password('password'); ?>
<?php echo $Form->checkbox('remember', 'remember'); ?>
<?php echo $Form->checkboxLabel('remember', 'remember', 'Remember'); ?>

<?php echo $Form->submit('Log in'); ?>

<?php echo $Form->end(); ?>

<?php if (isset($this->user)): ?>

<p>Logged in as: <?php echo $this->user->username; ?></p>

<p><?php echo $Html->link('Log out', array('query' => array('logout' => null))); ?></p>

<?php endif; ?>
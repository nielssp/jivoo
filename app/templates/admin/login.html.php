<?php
$this->style('admin/theme.css'); 
$this->style('admin/icomoon/style.css'); 
?>
<!DOCTYPE html>
<html>
<head>
<title>Jivoo</title>

<?php echo $this->block('style'); ?>

</head>
<body id="login">

<div>
<h1><a href="#">Jivoo</a></h1>
<?php echo $Form->form(); ?>

<?php foreach ($messages as $message): ?>
<div class="flash flash-error"><?php echo $message->message; ?></div>
<?php endforeach; ?>

<div class="field">
<?php echo $Form->label('username', tr('Username')); ?>
<?php echo $Form->text('username'); ?>
</div>
<div class="field">
<?php echo $Form->label('password', tr('Password')); ?>
<?php echo $Form->password('password'); ?>
</div>
<div class="remember">
<?php echo $Form->checkbox('remember', 'remember'); ?>
<?php echo $Form->checkboxLabel('remember', 'remember', tr('Remember')); ?>
</div>
<button type="submit">
<span class="icon icon-enter"></span>
<span class="label">
Log in
</span>
</button>
<?php echo $Form->end(); ?>

<p><a href="#">Reset password</a></p>

</div>


</body>
</html>
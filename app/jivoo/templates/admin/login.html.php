<?php
$this->meta('viewport', 'width=device-width, initial-scale=1');
$this->style('admin/theme.css'); 
$this->style('admin/icomoon/style.css'); 
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Log in | <?php echo $site['title']; ?></title>

<?php echo $this->block('meta'); ?>
<?php echo $this->block('style'); ?>
<?php echo $this->block('script'); ?>

</head>
<body id="login">

<div>
<h1><?php echo $Html->link($site['title'], null); ?></h1>
<?php echo $Form->form(); ?>

<?php foreach ($flash as $message): ?>
<div class="flash flash-<?php echo $message->type; ?>"><?php echo $message; ?></div>
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

<p><a href="#not-implemented">Reset password</a></p>

</div>


</body>
</html>
<?php
$this->meta('viewport', 'width=device-width, initial-scale=1');
$this->import('icomoon/style.css', 'jivoo/jtk/theme.css');
?>
<body id="login">

<div>
<h1><?php echo $Html->link($title, $titleRoute); ?></h1>

<?php foreach ($flash as $message): ?>
<div class="flash flash-<?php echo $message->type; ?>"><?php echo $message; ?></div>
<?php endforeach; ?>

<?php echo $Form->form(); ?>

<?php echo $Jtk->TextField(array(
  'field' => 'username',
  'label' => tr('Username')
));?>

<?php echo $Jtk->PasswordField(array(
  'field' => 'password',
  'label' => tr('Password')
));?>

<div class="remember">
<?php echo $Form->checkbox('remember', 'remember'); ?>
<?php echo $Form->checkboxLabel('remember', 'remember', tr('Remember')); ?>
</div>

<?php echo $Icon->button(tr('Log in'), 'enter', array('type' => 'submit')); ?>

<?php echo $Form->end(); ?>

<?php foreach ($links as $label => $link): ?>
<p><?php echo $Html->link($label, $link); ?></p>
<?php endforeach; ?>

</div>

</body>
<?php
/* 
 * Template for blog post
 */

// Render the header
$this->renderTemplate('header');
?>

<form action="<?php echo $PEANUT['http']->getLink(); ?>"  method="post">

<?php if (isset($PEANUT['user']->loginError))
        echo '<p>' . $PEANUT['user']->loginError . '</p>';
?>

<input type="hidden" name="action" value="login" />

<?php echo $PEANUT['functions']->call('formInput', 'text', 'username', tr('Username'), $PEANUT['user']->loginUsername); ?>

<?php echo $PEANUT['functions']->call('formInput', 'password', 'password', tr('Password')); ?>

<?php echo $PEANUT['functions']->call('formInput', 'submit', 'login', tr('Log in')); ?>

</form>

<?php
// Render the footer
$this->renderTemplate('footer');
?>
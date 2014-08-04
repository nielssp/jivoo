<?php $this->extend('admin/layout.html'); ?>

<p>Welcome to the Jivoo App Development Kit.</p>

<h2>System</h2>

<p><?php echo php_uname(); ?></p>

<p>PHP <?php echo phpversion(); ?></p>

<h2>Libraries (<?php echo LIB_PATH; ?>)</h2>

<?php foreach ($libs as $lib): ?>
<p><?php echo $lib; ?></p>
<?php endforeach; ?>

<h2>Applications</h2>

<?php foreach ($apps as $app): ?>
<p>
<?php echo $app['name'] . ' ' . $app['version']; ?>
</p>
<?php endforeach; ?>


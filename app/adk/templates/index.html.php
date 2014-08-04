<p>Welcome to Jivoo ADK</p>

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


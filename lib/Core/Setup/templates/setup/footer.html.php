
</div>

<div class="footer" id="poweredby">
<?php if (isset($app['website'])): ?>
<?php echo $Html->link(
  $app['name'] . ' ' . $app['version'],
  $app['website']
); ?>
<?php else: ?>
<?php echo $app['name']; ?> 
<?php echo $app['version']; ?>
<?php endif; ?>
</div>

<div class="footer" id="links">
<a href="http://apakoh.dk">Apakoh Core</a>
</div>

</body>
</html>
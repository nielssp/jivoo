</div>

<div id="sidebar">
<?php $this->output('sidebar'); ?>
</div>

<div class="clear"></div>

<div id="footer">
  <h2>
    <?php echo $Html->link($site['title']); ?></h2>
  <div id="powered-by">
    <?php echo $Html->link('Powered by Apakoh Core.', 'http://apakoh.dk'); ?>
  </div>
</div>
</div>

    
<?php $this->output('body-bottom'); ?>
  </body>
</html>

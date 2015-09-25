<?php $this->view->data->title = tr('Progress'); ?>
<script type="text/javascript">
$(function() {
  $('[data-play]').click(function() {
    var action = $(this).data('play');
    $(this).parent().parent().find('.progress').each(function() {
      var $progress = $(this);
      var current = $progress.data('progress');
      var setProgress = function(progress) {
        progress = Math.max(0, Math.min(100, progress));
        console.log(progress);
        $progress.data('progress', progress);
        $progress.find('.progress-bar').outerWidth(progress + '%');
        if ($progress.data('notext') === undefined)
          $progress.find('.progress-bar').text(progress + '%');
        if (progress == 100) {
          $progress.find('.label').text('Done!');
          $progress.attr('class', 'progress success');
        }
        else {
          $progress.find('.label').html('Doing things&hellip;');
          $progress.attr('class', 'progress ' + $progress.data('class'));
        }
      };
      if (action == 'forward')
        setProgress(current + 10);
      if (action == 'back')
        setProgress(current - 10);
      if (action == 'animate') {
        var interval = setInterval(function() {
          current += Math.floor(Math.random() * 10);
          setProgress(current);
          if (current >= 100)
            clearInterval(interval);
        }, 200);
      }
    });
  });
});
</script>
<div class="grid-1-1 grid-sm">
<div class="cell">
<div class="block">
<div class="block-header"><h2>Progress bars</h2></div>
<div class="block-content">
<div class="progress primary" data-progress="50" data-notext data-class="primary">
<div class="progress-bar" style="width:50%;"></div>
</div>
<div class="progress primary" data-progress="50" data-class="primary">
<div class="progress-bar" style="width:50%;">50%</div>
</div>
<div class="progress primary active" data-progress="50" data-class="primary active">
<div class="progress-bar" style="width:50%;">50%</div>
</div>
<div class="progress primary active" data-progress="50" data-class="primary active">
<div class="progress-bar" style="width:50%;">50%</div>
<div class="label">Doing things&hellip;</div>
</div>
</div>
<div class="block-footer">
<button data-play="animate"><span class="icon"><?php echo $Icon->icon("rocket"); ?></span></button>
<button data-play="back"><span class="icon"><?php echo $Icon->icon("arrow-left"); ?></span></button>
<button data-play="forward"><span class="icon"><?php echo $Icon->icon("arrow-right"); ?></span></button>
</div>
</div>
</div>
<div class="cell">
<div class="block">
<div class="block-header"><h2>Progress bar contexts</h2></div>
<div class="block-content">
<?php foreach (array('default', 'primary', 'light', 'dark', 'info', 'success', 'warning', 'error') as $context): ?>
<div class="<?php echo 'progress active ' . $context; ?>" data-progress="50" data-class="<?php echo $context; ?> active">
<div class="progress-bar" style="width:50%;">50%</div>
<div class="label">Doing things&hellip;</div>
</div><?php endforeach; ?>

</div>
<div class="block-footer">
<button data-play="animate"><span class="icon"><?php echo $Icon->icon("rocket"); ?></span></button>
<button data-play="back"><span class="icon"><?php echo $Icon->icon("arrow-left"); ?></span></button>
<button data-play="forward"><span class="icon"><?php echo $Icon->icon("arrow-right"); ?></span></button>
</div>
</div>
</div>
</div>
<?php echo $Form->form('Posts::archive', array('method' => 'get')); ?>

<?php echo $Form->text('q', array('placeholder' => tr('Search'))); ?>

<?php echo $Form->end(); ?>
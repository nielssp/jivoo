<?php
// Render the header
$this->render('backend/header.html');
?>

      <div class="section header_section">
        <div class="container">
          <h1><?php echo $Html->link($site['title']); ?></h1>
        </div>
      </div>

      <?php echo $Form->begin($login); ?>

      <div class="top_shadow"></div>
      <div class="section dark_section">
        <div class="container narrow_container">
          <p>
            <?php echo $Form->label('username', null, array('class' => 'small')); ?>
            <?php echo $Form->field('username',
    array('class' => 'text bigtext')); ?>
          </p>
          <p>
            <?php echo $Form->label('password', null, array('class' => 'small')); ?>
            <?php echo $Form->field('password',
    array('class' => 'text bigtext')); ?>
          </p>
        </div>
      </div>
      <div class="bottom_shadow"></div>

      <div class="section">
        <div class="container narrow_container">
          <div class="aright">
            <?php echo $Form->submit(tr('Reset password'), 'reset'); ?>
            <?php echo $Form->submit(tr('Log in'), 'submit',
    array('class' => 'button publish')); ?>
          </div>
        </div>
      </div>
<?php
$this->render('backend/footer.html');
?>


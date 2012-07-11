<?php
// Render the header
$this->render('backend/header.html');
?>
      
    <?php echo $Form->begin($page); ?>

      <div class="section">
        <div class="container">
          <p>
            <?php echo $Form->label('title', NULL, array('class' => 'small')); ?>
            <?php echo $Form->field('title', array('class' => 'text bigtext')); ?>
          </p>
          <p>
            <?php echo $Form->label('content', NULL, array('class' => 'small')); ?>
            <?php echo $Form ->field('content'); ?>
          </p>
        </div>
      </div>
      
      <div id="settings">
        <div class="top_shadow"></div>
        <div class="section dark_section">
          <div class="container">
            <div class="input">
              <p class="label">
                <?php echo $Form->label('name'); ?>
              </p>
              <div class="element">
                <div class="permalink-wrapper">
                  <?php echo $beforePermalink;
                  echo $Form->field('name', array(
                    'class' => 'text permalink permalink-allow-slash',
                    'data-title-id' => $Form->fieldId('title')
                  ));
                  ?>
                </div>
              </div>
              <div class="clearl"></div>
            </div>
          </div>
        </div>
        <div class="bottom_shadow"></div>
      </div>
      
      <div class="section">
        <div class="container">
          <div class="left">
            <input type="checkbox" class="button" id="check_settings" />
            <label for="check_settings">Settings</label>
          </div>
          <div class="aright">
            <?php echo $Form->submit(tr('Save draft'), 'save'); ?>
            <?php echo $Form->submit(tr('Publish'), 'publish', array('class' => 'button publish')); ?>
          </div>
        </div>
      </div>
    <?php echo $Form->end(); ?>

<?php
$this->render('backend/footer.html');
?>

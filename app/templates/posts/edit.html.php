<?php
// Render the header
$this->render('backend/header.html');
?>
      
    <?php echo $Form->begin($post); ?>

      <div class="section">
        <div class="container">
          <p>
            <?php echo $Form->label('title', null, array('class' => 'small')); ?>
            <?php echo $Form->field('title', array('class' => 'text bigtext')); ?>
          </p>
          <p>
            <?php echo $Form->label('content', null, array('class' => 'small')); ?>
            <?php echo $Form ->field('content'); ?>
          </p>
          <p>
            <?php echo $Form->label('tags', null, array('class' => 'small')); ?>
            <?php echo $Form ->field('tags'); ?>
            <span class="description">Comma-separated list of tags</span>
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
                  if ($nameInPermalink) {
                    echo $Form->field('name', array(
                      'class' => 'text permalink',
                      'data-title-id' => $Form->fieldId('title')
                    ));
                  }
                  echo $afterPermalink;
                  ?>
                </div>
              </div>
              <div class="clearl"></div>
            </div>
            <div class="separator"></div>
            <div class="input">
              <p class="label">
                <?php echo tr('Allow comments'); ?>
              </p>
              <div class="element">
                <div class="radioset">
                  <?php echo $Form->radio('commenting', 'yes'); ?>
                  <?php echo $Form->label('commenting_yes', tr('Yes')); ?>
                  <?php echo $Form->radio('commenting', 'no'); ?>
                  <?php echo $Form->label('commenting_no', tr('No')); ?>
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


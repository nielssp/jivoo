<?php
// Render the header
$this->render('backend/header.html');
?>

      <div class="section light_section">
        <div class="container pagination" id="pagination">
          <span class="older block15">&nbsp;</span>
          <span class="pages block15"><?php echo tr('Filter:'); ?></span>
          <form action="<?php echo h($this->link(array())); ?>" method="get">
            <span class="filter block30 margin5">
              <input type="search" class="text" name="filter" value="<?php echo h(); ?>" />
            </span>
          </form>
          <span class="newer">&nbsp;</span>
          <div class="clearl"></div>
        </div>
      </div>

      <?php echo $Form->begin(); ?>

      <div class="section light_section">
        <div class="container">
          <h2>Site</h2>
          <div class="input">
            <p class="label"><label for="post_permalink">Site Title</label></p>
            <div class="element">
              <input type="text" class="text" />
            </div>
            <div class="clearl"></div>
          </div>
          <div class="separator"></div>
          <div class="input">
            <p class="label"><label for="post_permalink">Site Subtitle</label></p>
            <div class="element">
              <input type="text" class="text" />
            </div>
            <div class="clearl"></div>
          </div>
        </div>
      </div>

      <div class="section">
        <div class="container">
          <div class="aright">
            <?php echo $Form->submit(tr('Save'), 'publish', array('class' => 'button publish')); ?>
          </div>
        </div>
      </div>

      <?php echo $Form->end(); ?>

<?php
$this->render('backend/footer.html');
?>


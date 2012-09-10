
<?php $started = $Bulk->isStarted(); ?>

<?php if (!$started) echo $Bulk->begin(); ?>

      <div class="section bulk-actions">
        <div class="container">
          <div class="checkbox">
            <input type="checkbox" value="all" name="all"
              id="select-all-<?php echo $started ? 'bottom' : 'top'; ?>" />
          </div>
          <div class="checkbox-text">
            <label for="select-all-top" data-phrase1="<?php echo h(tr('Select all')); ?>"
              data-phrase2="<?php echo h(tr('%1 selected', 0)); ?>">
              <?php echo trn('Select one comment', 'Select all %1 comments', $Pagination->getCount()); ?>
            </label>
          </div>
          <div class="actions">
            <ul class="menubutton">
            <?php $first = true; ?>
            <?php foreach ($Bulk->getActions() as $action): ?>
<?php
$classes = '';
if ($first) {
  $first = false;
  $classes .= ' first';
}
if ($action['type'] == 'delete') {
  $classes .= ' red';
}
?>
              <li class="<?php echo $classes; ?>">
                <input type="submit" name="<?php echo $action['name']; ?>"
                  value="<?php echo $action['label']; ?>" />
              </li>
            <?php endforeach; ?>
            </ul>
          </div>
          <div class="clearl"></div>
        </div>
      </div>

<?php if ($started) echo $Bulk->end(); ?>
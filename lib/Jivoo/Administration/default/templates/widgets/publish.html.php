
<?php echo $Form->formFor($options['record'], $options['route'], array('class' => 'publish')); ?>

<div class="toolbar">
  <button type="submit" class="primary" name="save">
    <span class="icon icon-disk"></span>
    <span class="label">Save</span>
  </button>
  <button type="submit" name="save-close">
    <span class="icon icon-checkmark"></span>
    <span class="label">Save &amp; close</span>
  </button>
  <button type="submit" name="save-new">
    <span class="icon icon-plus"></span>
    <span class="label">Save &amp; new</span>
  </button>
</div>

<div class="article">

<?php echo $Form->text($options['title'], array(
  'placeholder' => $options['record']->getModel()->getLabel($options['title']),
  'class' => 'title'
)); ?>

<?php echo $Form->textarea($options['content'], array(
  'placeholder' => $options['record']->getModel()->getLabel($options['content']),
  'class' => 'content'
)); ?>

</div>

<div class="settings">

  <div class="field">
    <label>Permalink</label>
    <input type="text" />
  </div>

  <div class="field">
    <label>Status</label>
    <select size="1">
      <option>Published</option>
      <option>Pending review</option>
      <option>Draft</option>
    </select>
  </div>

  <div class="field">
    <label>Tags</label>
    <input type="text" />
  </div>

  <div class="field">
    <label>Comments</label>
    <input type="checkbox" /><label>Allow comments</label>
  </div>
</div>

<?php echo $Form->end(); ?>
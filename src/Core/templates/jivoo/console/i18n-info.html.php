<?php echo $Form->form(array(), array('method' => 'get')); ?>

<div class="field">
<?php echo $Form->label('scope', tr('Scope')); ?>
<?php echo $Form->select('scope'); ?>
<?php echo $Form->option('app', tr('Application')); ?>
<?php echo $Form->option('lib', tr('Library')); ?>
<?php if (isset($extensions)): ?>
<?php echo $Form->optgroup(tr('Extensions'))?>
<?php foreach ($extensions as $id => $extension): ?>
<?php echo $Form->option('extension-' . $id, $extension->name); ?>
<?php endforeach; ?>
<?php echo $Form->end(); ?>
<?php endif; ?>
<?php if (isset($themes)): ?>
<?php echo $Form->optgroup(tr('Themes'))?>
<?php foreach ($themes as $id => $theme): ?>
<?php echo $Form->option('theme-' . $id, $theme->name); ?>
<?php endforeach; ?>
<?php echo $Form->end(); ?>
<?php endif; ?>
<?php echo $Form->end(); ?>
</div>

<?php echo $Form->submit(tr('Set scope')); ?>


<?php echo $Form->end(); ?>

<h2>Languages</h2>

<p>
<?php echo tr('The current directory is: %1', '<code>' . $dir . '</code>'); ?>
</p>

<?php if (!$dirExists): ?>

<p><?php echo tr('The directory does not exist.'); ?></p> 

<?php else: ?>

<table class="density-high">
<thead>
<tr>
<th>Language</th>
<th>Region</th>
<th>Code</th>
<th>CSV</th>
<th>PHP</th>
</tr>
</thead>
<tbody>
<?php foreach ($languages as $code => $localization): ?>
<tr>
<td><?php echo h($localization->name); ?></td>
<td><?php echo h($localization->region); ?></td>
<td><?php echo $code; ?></td>
<td>No</td>
<td>Yes</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<h2><?php echo tr('New translation'); ?></h2>

<?php echo $Form->form(array('mergeQuery' => true), array('name' => 'new')); ?>

<?php echo $Form->field('code', array('label' => tr('Language code (IETF)'))); ?>
<?php echo $Form->field('language', array('label' => tr('Language'))); ?>
<?php echo $Form->field('region', array('label' => tr('Region'))); ?>

<div class="field">
<?php echo $Form->label('extend', tr('Inherit from')); ?>
<?php echo $Form->select('extend'); ?>
<?php echo $Form->option('', ''); ?>
<?php foreach ($languages as $code => $localization): ?>
<?php echo $Form->option($code, h($localization->name . ' (' . $code . ')')); ?>
<?php endforeach; ?>
<?php echo $Form->end(); ?>
<?php echo tr('Leave blank to copy all strings from root language.'); ?>
</div>

<?php echo $Form->submit(tr('Create')); ?>

<?php echo $Form->end(); ?>

<h2><?php echo tr('Generate root language'); ?></h2>

<p>
<?php echo tr('Generates the root language file ("%1") from all uses of %2 and %3 in the selected scope.', 'en', '<code>tr()</code>', '<code>tn()</code>'); ?>
</p>

<?php echo $Form->form(array('mergeQuery' => true)); ?>

<?php echo $Form->submit(tr('Generate'), array('name' => 'generate')); ?>

<?php echo $Form->end(); ?>


<?php endif;?>
<?php $this->extend('admin/layout.html'); ?>

<table>
<tbody>
<?php foreach ($extensions as $info): ?>
<tr>
<td class="primary">
<h2>
<?php echo h($info->name); ?>

<span class="version"><?php echo h($info->version); ?></span>
</h2>
<div class="description">
<?php echo h($info->description); ?>
</div>
</td>
<td>
<?php if ($info->enabled): ?>
<?php echo $Form->actionButton(tr('Disable'), array('action' => 'disable', $info->dir)); ?>
<?php else: ?>
<?php echo $Form->actionButton(tr('Enable'), array('action' => 'enable', $info->dir)); ?>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
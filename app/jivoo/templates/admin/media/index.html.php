<?php $this->extend('admin/layout.html'); ?>

<div class="table-operations">
<?php echo $Icon->link(h('Add'), 'add', 'plus', null, array('class' => 'button')); ?>
</div>

<table>
<thead>
<tr>
<th>Preview</th>
<th class="primary">File</th>
<th>Size</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($files as $file): ?>
<tr>
<td><img src="<?php echo $this->file('media', $file['relativePath']); ?>" style="height:50px;"/></td>
<td class="primary"><?php echo h($file['name']); ?></td>
<td>unknown</td>
<td></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
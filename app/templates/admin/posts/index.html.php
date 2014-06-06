<?php $this->extend('admin/layout.html'); ?>

<div class="toolbar">

<input type="text" placeholder="Filter" />
<button type="submit">
<span class="icon icon-search"></span>
<span class="label">Search</span>
</button>

<div class="dropdown">
  <a href="#">View: All</a>
  <ul>
    <li class="selected"><a href="#">All</a></li>
    <li><a href="#">Published</a></li>
    <li><a href="#">Draft</a></li>
  </ul>
</div>

</div>

<div class="table-operations">

<div class="dropdown dropdown-actions">
  <a href="#">With selection</a>
  <ul>
    <li><?php echo $Icon->button('Edit', 'pencil'); ?></li>
    <li><?php echo $Icon->button('Publish', 'eye'); ?></li>
    <li><?php echo $Icon->button('Unpublish', 'eye-blocked'); ?></li>
    <li><?php echo $Icon->button('Delete', 'remove'); ?></li>
  </ul>
</div>

<div class="dropdown">
  <a href="#">Sort by</a>
  <ul>
    <li><a href="#">Title</a></li>
    <li><a href="#">Author</a></li>
    <li><a href="#">Status</a></li>
    <li class="selected selected-asc"><a href="#">Date</a></li>
  </ul>
</div>
</div>

<div class="pagination">
  <a href="#" class="icon icon-cog"></a>
  <a href="#" class="icon icon-binoculars"></a>
<strong>1&ndash;20</strong> of <strong>50</strong>
<button class="prev" disabled="disabled">
<span class="icon icon-arrow-left2"></span>
</button>
<button class="next">
<span class="icon icon-arrow-right2"></span>
</button>

</div>

<table>

  <thead>
    <tr>
      <th class="selection" scope="col">
        <label>
          <input type="checkbox" />
        </label>
      </th>
      <th class="primary" scope="col"><a href="#">Title</a></th>
      <th scope="col"><a href="#">Author</a></th>
      <th scope="col"><a href="#">Status</a></th>
      <th scope="col"><a href="#" class="selected-desc">Date</a></th>
      <th class="actions" scope="col">Actions</th>
    </tr>
  </thead>

  <tbody>
<?php foreach ($posts as $post): ?>
    <tr>
      <td class="selection">
        <label>
          <input type="checkbox" />
        </label>
      </td>
      <td class="primary">
        <?php echo $Html->link(h($post->title), $post); ?>
        <div class="essential">
          <span>Date: <?php echo I18n::longDate($post->createdAt); ?></span>
          <span>Author: 
            <?php echo $Html->link(h($post->user->username), $post->user); ?></span>
          <span>Status: <?php echo $post->status; ?></span>
        </div>
        <div class="action-links">
          <a href="#">Edit</a>
          <a href="#">View</a>
          <a href="#">Publish</a>
          <a href="#">Delete</a>
        </div>
      </td>
      <td>
        <?php echo $Html->link(h($post->user->username), $post->user); ?>
      </td>
      <td><?php echo $post->status; ?></td>
      <td>
        <?php echo I18n::longDate($post->createdAt); ?>
      </td>
      <td class="actions">
        <?php echo $Html->link('', array('action' => 'edit', $post->id), array('class' => 'icon-pencil')); ?>
        <a href="#" class="icon-screen"></a>
        <a href="#" class="icon-eye"></a>
        <a href="#" class="icon-remove2"></a>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>

  <tfoot>
    <tr>
      <th class="selection">
        <label>
          <input type="checkbox" />
        </label>
      </th>
      <th class="primary">Title</th>
      <th>Author</th>
      <th>Status</th>
      <th>Date</th>
      <th class="actions">Actions</th>
    </tr>
  </tfoot>

</table>

<div class="pagination">
  <a href="#" class="icon icon-cog"></a>
  <a href="#" class="icon icon-binoculars"></a>
<strong>1&ndash;20</strong> of <strong>50</strong>
<button class="prev" disabled="disabled">
<span class="icon icon-arrow-left2"></span>
</button>
<button class="next">
<span class="icon icon-arrow-right2"></span>
</button>
</div>

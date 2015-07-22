<?php $this->view->data->title = tr('Simple tables'); ?>

<?php
$randomUsers = function($n = 5) use($Random) {
  $users = array();
  $roles = array(tr('User'), tr('Mod'), tr('Admin'));
  $roleContexts = array('info', 'warning', 'error');
  for ($i = 0; $i < $n; $i++) {
    if (rand(0, 3) == 1) $role = 2;
    else if (rand(0, 1) == 1) $role = 1;
    else $role = 0;
    $name = $Random->name();
    $email = strtolower(preg_replace('/^([a-z])[a-z]* ([a-z]+)$/i', '\1\2@example.com', $name));
    $users[] = array(
      'id' => $i + 1,
      'name' => $name,
      'email' => $email,
      'role' => $roles[$role],
      'context' => $roleContexts[$role]
    );
  }
  return $users;
};
?>

<div class="grid-1-1 grid-sm">
<div class="cell">
<div class="block"><div class=" block-header"><h2><?php echo tr('Default table'); ?></h2></div><div class="block-content">

<table>
<thead>
<tr>
<th scope="col" style="width: 50px;" class="center">#</th>
<th scope="col">User</th>
<th scope="col" class="col-sm">Role</th>
<th scope="col" style="width: 100px;" class="center">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($randomUsers(5) as $user): ?>
<tr>
<td class="center"><?php echo h($user['id']); ?></td>
<td scope="row"><?php echo h($user['name']); ?></td>
<td>
<span class="<?php echo 'badge ' . 'badge-' . $user['context']; ?>"><?php echo h($user['role']); ?></span>
</td>
<td class="center">
<div class="button-group">
                <?php echo $Icon->button('', 'pencil', array('class' => 'button-xs')); ?>
                <?php echo $Icon->button('', 'close', array('class' => 'button-xs')); ?>
</div>
</td>
</tr><?php endforeach; ?>

</tbody>
</table>
</div></div>
</div>
<div class="cell">
<div class="block"><div class=" block-header"><h2><?php echo tr('Contextual table'); ?></h2></div><div class="block-content">

<table>
<thead>
<tr>
<th scope="col" style="width: 50px;" class="center">#</th>
<th scope="col">User</th>
<th scope="col" class="col-sm">Role</th>
<th scope="col" style="width: 100px;" class="center">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($randomUsers(5) as $user): ?>
<tr class="<?php echo $user['context']; ?>">
<td class="center"><?php echo h($user['id']); ?></td>
<td scope="row"><?php echo h($user['name']); ?></td>
<td>
<span class="<?php echo 'badge ' . 'badge-' . $user['context']; ?>"><?php echo h($user['role']); ?></span>
</td>
<td class="center">
<div class="button-group">
                <?php echo $Icon->button('', 'pencil', array('class' => 'button-xs')); ?>
                <?php echo $Icon->button('', 'close', array('class' => 'button-xs')); ?>
</div>
</td>
</tr><?php endforeach; ?>

</tbody>
</table>
</div></div>
</div>
</div>
<div class="grid-1-1 grid-sm">
<div class="cell">
<div class="block"><div class=" block-header"><h2><?php echo tr('Checkable table'); ?></h2></div><div class="block-content">

<table>
<thead>
<tr>
<th scope="col" class="selection"><label><input type="checkbox" /></label></th>
<th scope="col">User</th>
<th scope="col" class="col-sm">Role</th>
<th scope="col" style="width: 100px;" class="center">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($randomUsers(5) as $user): ?>
<tr>
<td class="selection"><label><input type="checkbox" /></label></td>
<td scope="row"><?php echo h($user['name']); ?></td>
<td>
<span class="<?php echo 'badge ' . 'badge-' . $user['context']; ?>"><?php echo h($user['role']); ?></span>
</td>
<td class="center">
<div class="button-group">
                <?php echo $Icon->button('', 'pencil', array('class' => 'button-xs')); ?>
                <?php echo $Icon->button('', 'close', array('class' => 'button-xs')); ?>
</div>
</td>
</tr><?php endforeach; ?>

</tbody>
</table>
</div></div>
</div>
<div class="cell">
<div class="block"><div class=" block-header"><h2><?php echo tr('Scrollable table'); ?></h2></div><div class="block-content">

<div class="table-scrollable">
<table>
<thead>
<tr>
<th scope="col" style="width: 50px;" class="center">#</th>
<th scope="col" style="min-width:200px;">User</th>
<th scope="col" class="col-sm">Email</th>
<th scope="col" class="col-xs">Role</th>
<th scope="col" style="width: 100px;" class="center">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($randomUsers(5) as $user): ?>
<tr>
<td class="center"><?php echo h($user['id']); ?></td>
<td scope="row"><?php echo h($user['name']); ?></td>
<td><?php echo h($user['email']); ?></td>
<td>
<span class="<?php echo 'badge ' . 'badge-' . $user['context']; ?>"><?php echo h($user['role']); ?></span>
</td>
<td class="center">
<div class="button-group">
                  <?php echo $Icon->button('', 'pencil', array('class' => 'button-xs')); ?>
                  <?php echo $Icon->button('', 'close', array('class' => 'button-xs')); ?>
</div>
</td>
</tr><?php endforeach; ?>

</tbody>
</table>
</div>
</div></div>
</div>
</div>
<div class="block"><div class=" block-header"><h2><?php echo tr('Responsive table'); ?></h2></div><div class="block-content">

<table>
<thead>
<tr>
<th scope="col" style="width: 50px;" class="center">#</th>
<th scope="col">User</th>
<th scope="col" class="col-md non-essential">Email</th>
<th scope="col" class="col-sm non-essential">Role</th>
<th scope="col" style="width: 100px;" class="center">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($randomUsers(5) as $user): ?>
<tr>
<td class="center"><?php echo h($user['id']); ?></td>
<td class="main" scope="row">
<strong><?php echo h($user['name']); ?></strong>
<dl class="values">
<dt>Email</dt>
<dd><?php echo h($user['email']); ?></dd>
<dt>Role</dt>
<dd><span class="<?php echo 'badge ' . 'badge-' . $user['context']; ?>"><?php echo h($user['role']); ?></span></dd>
</dl>
</td>
<td class="non-essential"><?php echo h($user['email']); ?></td>
<td class="non-essential"><span class="<?php echo 'badge ' . 'badge-' . $user['context']; ?>"><?php echo h($user['role']); ?></span></td>
<td class="center">
<div class="button-group">
            <?php echo $Icon->button('', 'pencil', array('class' => 'button-xs')); ?>
            <?php echo $Icon->button('', 'close', array('class' => 'button-xs')); ?>
</div>
</td>
</tr><?php endforeach; ?>

</tbody>
</table>
</div></div>
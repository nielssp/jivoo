<?php $this->view->data->title = tr('Buttons'); ?>
 
<p>
  <a href="#" class="button">A button</a>
  <button>A button</button>
  <input type="submit" value="A button" />
  <input type="button" value="A button" />
</p>

<p>
  Disabled: 
  <button disabled>A button</button>
  <input type="submit" value="A button" disabled />
  <input type="button" value="A button" disabled />
</p>

<h2>Buttons with icons</h2>

<p>
  <?php echo $Icon->button('A button', 'plus'); ?>
  <?php echo $Icon->button('A button', 'download'); ?>
  <?php echo $Icon->button('A button', 'enter'); ?>
</p>

<p>
  Disabled:
  <?php echo $Icon->button('A button', 'plus', array('disabled' => 'disabled')); ?>
  <?php echo $Icon->button('A button', 'download', array('disabled' => 'disabled')); ?>
  <?php echo $Icon->button('A button', 'enter', array('disabled' => 'disabled')); ?>
</p>

<h2>Dropdowns</h2>


<div class="dropdown disabled">
<a>
<span class="icon icon-flag"></span>
A button
</a>
<?php
$menu = $Jtk->Menu;
$menu->appendAction('A menu item')->setRoute('url:#')->setIcon('plus');
$menu->appendAction('A menu item')->setRoute('url:#')->setIcon('download');
$menu->appendAction('A menu item')->setRoute('url:#')->setIcon('enter');
$menu->appendMenu('A submenu')->setIcon('newspaper')
  ->appendAction('A submenu item')->setRoute('url:#');
echo $menu();
?>
</div>
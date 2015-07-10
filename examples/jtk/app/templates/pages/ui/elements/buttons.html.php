<?php $this->view->data->title = tr('Buttons'); ?>

<div class="row-1-1">
  <div class="cell">
    <div class="block">
      <div class="block-header"><h3>Button types</h3></div>
      <div class="block-content">
        <div class="row-1-1">
          <div class="cell">
            <p><a href="#" class="button">Button</a></p>
            <p><code>&lt;a class="button"&gt;</code></p>
            <p><input type="submit" value="Button" /></p>
            <p><code>&lt;input type="submit"&gt;</code></p>
          </div>
          <div class="cell">
            <p><button>Button</button></p>
            <p><code>&lt;button&gt;</code></p>
            <p><input type="button" value="Button" /></p>
            <p><code>&lt;input type="button"&gt;</code></p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="cell">
    <div class="block">
      <div class="block-header"><h3>Disabled buttons</h3></div>
      <div class="block-content">
        <button disabled>Button</button>
        <input type="submit" value="Submit button" disabled />
        <input type="button" value="Input button" disabled />
      </div>
    </div>
    <div class="block">
      <div class="block-header"><h3>Button contexts</h3></div>
      <div class="block-content">
        <button>Default</button>
        <button class="primary">Primary</button>
        <button class="info">Info</button>
        <button class="success">Success</button>
        <button class="warn">Warn</button>
        <button class="error">Error</button>
      </div>
    </div>
  </div>
</div>
 

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

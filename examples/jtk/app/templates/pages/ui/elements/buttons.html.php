<?php $this->view->data->title = tr('Buttons'); ?>

<div class="grid-sm grid-1-1">
  <div class="cell">
    <div class="block">
      <div class="block-header"><h3>Button types</h3></div>
      <div class="block-content">
        <div class="grid-xs grid-1-1">
          <div class="cell center">
            <p><a href="#" class="button">Button</a></p>
            <p><code>&lt;a class="button"&gt;</code></p>
            <p><input type="submit" value="Button" /></p>
            <p><code>&lt;input type="submit"&gt;</code></p>
          </div>
          <div class="cell center">
            <p><button>Button</button></p>
            <p><code>&lt;button&gt;</code></p>
            <p><input type="button" value="Button" /></p>
            <p><code>&lt;input type="button"&gt;</code></p>
          </div>
        </div>
      </div>
    </div>
    <div class="block">
      <div class="block-header"><h3>Buttons with icons</h3></div>
      <div class="block-content">
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
      </div>
    </div>
    <div class="block">
      <div class="block-header"><h3>Button groups</h3></div>
      <div class="block-content">
        <p>
          A button group:
          <span class="button-group button-group-check">
            <?php echo $Icon->button('Save'); ?>
            <?php echo $Icon->button('Cancel'); ?>
            <?php echo $Icon->button('OK'); ?>
          </span>
        </p>

        <p>
          Another button group:
          <span class="button-group button-group-radio">
            <?php echo $Icon->button('Save'); ?>
            <?php echo $Icon->button('Cancel', null, array('class' => 'active')); ?>
            <?php echo $Icon->button('OK'); ?>
          </span>
        </p>
          <script type="text/javascript">
          $(function() {
            $('.button-group-check').each(function() {
              var $buttons = $(this).children();
              $buttons.click(function() {
                $(this).toggleClass('active');
              });
            });
            $('.button-group-radio').each(function() {
              var $buttons = $(this).children();
              $buttons.click(function() {
                $buttons.removeClass('active');
                $(this).addClass('active');
              });
            });
          });
          </script>

        <p>
          Small icons: 
          <span class="button-group">
            <?php echo $Icon->button('', 'disk', array('class' => 'button-xs')); ?>
            <?php echo $Icon->button('', 'close', array('class' => 'button-xs')); ?>
            <?php echo $Icon->button('', 'checkmark', array('class' => 'button-xs')); ?>
          </span>
          With context:
          <span class="button-group">
            <?php echo $Icon->button('', 'disk', array('class' => 'button-primary button-xs')); ?>
            <?php echo $Icon->button('', 'close', array('class' => 'button-error button-xs')); ?>
            <?php echo $Icon->button('', 'checkmark', array('class' => 'button-success button-xs')); ?>
          </span>
        </p>
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
        <button class="button-primary">Primary</button>
        <button class="button-light">Light</button>
        <button class="button-dark">Dark</button>
        <button class="button-info">Info</button>
        <button class="button-success">Success</button>
        <button class="button-warning">Warning</button>
        <button class="button-error">Error</button>
      </div>
    </div>
    <div class="block">
      <div class="block-header"><h3>Button sizes</h3></div>
      <div class="block-content">
        <p>
          <button class="button-xs">Extra small</button>
          <button class="button-sm">Small</button>
          <button>Default</button>
          <button class="button-lg">Large</button>
        </p>
        <p>
          <?php echo $Icon->button('Extra small', 'plus', array('class' => 'button-xs')); ?>
          <?php echo $Icon->button('Small', 'download', array('class' => 'button-sm')); ?>
          <?php echo $Icon->button('Default', 'enter'); ?>
          <?php echo $Icon->button('Large', 'flag', array('class' => 'button-lg')); ?>
        </p>
        <p>
          <?php echo $Icon->button('', 'plus', array('class' => 'button-xs')); ?>
          <?php echo $Icon->button('', 'download', array('class' => 'button-sm')); ?>
          <?php echo $Icon->button('', 'enter'); ?>
          <?php echo $Icon->button('', 'flag', array('class' => 'button-lg')); ?>
        </p>
      </div>
    </div>
    <div class="block">
      <div class="block-header"><h3>Dropdown buttons</h3></div>
      <div class="block-content">
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
      </div>
    </div>
  </div>
</div>
 



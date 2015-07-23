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
          <?php echo $Jtk->button('A button', 'icon=plus'); ?>
          <?php echo $Jtk->button('A button', 'icon=download'); ?>
          <?php echo $Jtk->button('A button', 'icon=enter'); ?>
          <?php echo $Jtk->button('A button', 'icon=plus ctx=primary'); ?>
        </p>

        <p>
          Disabled:
          <?php echo $Jtk->button('A button', 'icon=plus disabled'); ?>
          <?php echo $Jtk->button('A button', 'icon=download disabled'); ?>
          <?php echo $Jtk->button('A button', 'icon=enter disabled'); ?>
        </p>
      </div>
    </div>
    <div class="block">
      <div class="block-header"><h3>Button groups</h3></div>
      <div class="block-content">
        <p>
          A button group:
          <span class="button-group button-group-check">
            <?php echo $Jtk->button('Save'); ?>
            <?php echo $Jtk->button('Cancel'); ?>
            <?php echo $Jtk->button('OK'); ?>
          </span>
        </p>

        <p>
          Another button group:
          <span class="button-group button-group-radio">
            <?php echo $Jtk->button('Save'); ?>
            <?php echo $Jtk->button('Cancel', 'class=active'); ?>
            <?php echo $Jtk->button('OK'); ?>
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
            <?php echo $Jtk->iconButton('Save', 'icon=disk size=xs'); ?>
            <?php echo $Jtk->iconButton('Cancel', 'icon=close size=xs'); ?>
            <?php echo $Jtk->iconButton('Ok', 'icon=checkmark size=xs'); ?>
          </span>
          With context:
          <span class="button-group">
            <?php echo $Jtk->iconButton('Save', 'icon=disk size=xs context=primary'); ?>
            <?php echo $Jtk->iconButton('Cancel', 'icon=close size=xs context=error'); ?>
            <?php echo $Jtk->iconButton('Ok', 'icon=checkmark size=xs context=success'); ?>
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
          <?php echo $Jtk->button('Extra small', 'icon=plus size=xs'); ?>
          <?php echo $Jtk->button('Small', 'icon=download size=sm'); ?>
          <?php echo $Jtk->button('Default', 'icon=enter'); ?>
          <?php echo $Jtk->button('Large', 'icon=flag size=lg'); ?>
        </p>
        <p>
          <?php echo $Jtk->iconButton('Extra small', 'icon=plus size=xs'); ?>
          <?php echo $Jtk->iconButton('Small', 'icon=download size=sm'); ?>
          <?php echo $Jtk->iconButton('Default', 'icon=enter'); ?>
          <?php echo $Jtk->iconButton('Large', 'icon=flag size=lg'); ?>
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
 



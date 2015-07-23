<?php $this->view->data->title = tr('Blocks'); ?>
<div class="block-container">
<div class="grid-1-1 grid-sm">
<div class="cell">
<div class="block"><div class="block-content">This is a simple block.</div></div>
<div class="block"><div class=" block-header"><h3>
        Block header
<small>Subheader</small>
</h3></div><div class="block-content">

      This is a block with a header and a footer.

</div><div class=" block-footer"><div>Block footer</div></div></div>
</div>
<div class="cell">
<div class="block"><div class=" block-header"><h3>
        Block header
<small>Subheader</small>
</h3></div><div class="block-content">

      This is a block with a header.
</div></div>
<div class="block"><div class="block-content">
      This is a block with a footer.

</div><div class=" block-footer"><div>Block footer</div></div></div>
</div>
</div>
<h2>Block toolbars</h2>
<div class="grid-md grid-1-1-1">
<div class="cell">
<div class="block">
<div class="block-header">
        A block
<div class="block-toolbar">
          <?php echo $Jtk->link('Undo', 'url:#', 'icon=undo'); ?>
          <?php echo $Jtk->iconLink('Redo', 'url:#', 'icon=redo'); ?>
          <?php echo $Jtk->iconLink('Spell check', 'url:#', 'icon=spell-check'); ?>
          <?php echo $Jtk->iconLink('Settings', 'url:#', 'icon=cog'); ?>
</div>
</div>
<div class="block-content">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.
          Donec sed pharetra lorem. Nunc auctor luctus tellus a
          faucibus. Quisque dictum in eros sed consequat. Vestibulum
          consequat, ipsum at porttitor iaculis, nibh neque accumsan
          dui, sed sodales orci ligula eu mauris.</p>
</div>
</div>
</div>
<div class="cell">
<div class="block">
<div class="block-header">
        A block
<div class="block-toolbar">
          <?php echo $Jtk->iconLink('Refresh', 'url:#', 'icon=loop'); ?>
          <?php echo $Jtk->iconLink('Close', 'url:#', 'icon=close'); ?>
</div>
</div>
<div class="block-content">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.
          Donec sed pharetra lorem. Nunc auctor luctus tellus a
          faucibus. Quisque dictum in eros sed consequat.</p>
<p>Vestibulum consequat, ipsum at porttitor iaculis, nibh
          neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
</div>
</div>
<div class="cell">
<div class="block">
<div class="block-header">
        A block
<div class="block-toolbar">Lorem ipsum</div>
</div>
<div class="block-content">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.
          Vestibulum consequat, ipsum at porttitor iaculis, nibh neque
          accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
</div>
</div>
</div>
<h2>Block header colors</h2>
<div class="grid-md grid-1-1-1-1">
<?php foreach (array('default', 'primary', 'light', 'dark') as $context): ?>
<div class="cell">
<div class="<?php echo 'block ' . 'block-' . $context; ?>">
<div class="block-header"><?php echo h(tr($context)); ?></div>
<div class="block-content">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.
          Donec sed pharetra lorem. Nunc auctor luctus tellus a
          faucibus.</p>
</div>
</div>
</div><?php endforeach; ?>

</div>
<div class="grid-md grid-1-1-1-1">
<?php foreach (array('info', 'success', 'warning', 'error') as $context): ?>
<div class="cell">
<div class="<?php echo 'block ' . 'block-' . $context; ?>">
<div class="block-header"><?php echo h(tr($context)); ?></div>
<div class="block-content">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.
          Donec sed pharetra lorem. Nunc auctor luctus tellus a
          faucibus.</p>
</div>
</div>
</div><?php endforeach; ?>

</div>
<div class="grid-md grid-3-1">
<div class="cell">
<div class="block">
<div class="block-header">Blocks inside a block</div>
<div class="block-content">
<div class="grid-xs grid-1-1-1">
<div class="cell">
<div class="block">
<div class="block-header">A block</div>
<div class="block-content">
<p>Lorem ipsum dolor sit amet, consectetur
                  adipiscing elit. Donec sed pharetra lorem. Nunc auctor
                  luctus tellus a faucibus. Quisque dictum in eros sed
                  consequat.</p>
</div>
</div>
</div>
<div class="cell">
<div class="block">
<div class="block-header">A block</div>
<div class="block-content">
<p>Lorem ipsum dolor sit amet, consectetur
                  adipiscing elit. Vestibulum consequat, ipsum at
                  porttitor iaculis, nibh neque accumsan dui, sed
                  sodales orci ligula eu mauris.</p>
</div>
</div>
</div>
<div class="cell">
<div class="block">
<div class="block-header">A block</div>
<div class="block-content">
<p>Lorem ipsum dolor sit amet, consectetur
                  adipiscing elit. Donec sed pharetra lorem. Vestibulum
                  consequat, ipsum at porttitor iaculis, nibh neque
                  accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<div class="cell">
<div class="block">
<div class="block-header">A block</div>
<div class="block-content">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.
          Donec sed pharetra lorem. Nunc auctor luctus tellus a
          faucibus. Quisque dictum in eros sed consequat. Vestibulum
          consequat, ipsum at porttitor iaculis, nibh neque accumsan
          dui, sed sodales orci ligula eu mauris.</p>
</div>
</div>
</div>
</div>
</div>
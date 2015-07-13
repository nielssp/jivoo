<?php $this->view->data->title = tr('Grid'); ?>

<div class="block-container">

<h2>Default grid <small>Always horizontal</small></h2>

<div class="grid grid-1-1-1-1">
<?php for ($i = 0; $i < 4; $i++): ?>
<div class="cell"><div class="block"><div class="block-content center"><code>.cell</code></div></div></div>
<?php endfor; ?>
</div>

<h2>XS grid</h2>

<div class="grid-xs grid-1-1-1-1">
<?php for ($i = 0; $i < 4; $i++): ?>
<div class="cell"><div class="block"><div class="block-content center"><code>.cell</code></div></div></div>
<?php endfor; ?>
</div>

<h2>SM grid</h2>

<div class="grid-sm grid-1-1-1-1">
<?php for ($i = 0; $i < 4; $i++): ?>
<div class="cell"><div class="block"><div class="block-content center"><code>.cell</code></div></div></div>
<?php endfor; ?>
</div>

<h2>MD grid</h2>

<div class="grid-md grid-1-1-1-1">
<?php for ($i = 0; $i < 4; $i++): ?>
<div class="cell"><div class="block"><div class="block-content center"><code>.cell</code></div></div></div>
<?php endfor; ?>
</div>

<h2>LG grid</h2>

<div class="grid-lg grid-1-1-1-1">
<?php for ($i = 0; $i < 4; $i++): ?>
<div class="cell"><div class="block"><div class="block-content center"><code>.cell</code></div></div></div>
<?php endfor; ?>
</div>


<h2>Grid proportions</h2>

<div class="block">
  <div class="block-header"><h3>1:1</h3></div>
  <div class="block-content">
<div class="grid grid-1-1">
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Nunc auctor luctus tellus a faucibus. Quisque dictum in eros sed consequat.</p>
<p>Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
</div>
  </div>
</div>

<div class="block">
  <div class="block-header"><h3>1:1:1</h3></div>
  <div class="block-content">
<div class="grid grid-1-1-1">
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Nunc auctor luctus tellus a faucibus. Quisque dictum in eros sed consequat.</p>
<p>Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
</div>
  </div>
</div>

<div class="block">
  <div class="block-header"><h3>1:1:1:1</h3></div>
  <div class="block-content">
<div class="grid grid-1-1-1-1">
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Nunc auctor luctus tellus a faucibus. Quisque dictum in eros sed consequat.</p>
<p>Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
<p>Quisque dictum in eros sed consequat.</p>
</div>
</div>
  </div>
</div>

<div class="block">
  <div class="block-header"><h3>1:2</h3></div>
  <div class="block-content">
<div class="grid grid-1-2">
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Nunc auctor luctus tellus a faucibus. Quisque dictum in eros sed consequat.</p>
<p>Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
</div>
  </div>
</div>

<div class="block">
  <div class="block-header"><h3>1:2:1</h3></div>
  <div class="block-content">
<div class="grid grid-1-2-1">
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Nunc auctor luctus tellus a faucibus. Quisque dictum in eros sed consequat.</p>
<p>Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
</div>
  </div>
</div>

<div class="block">
  <div class="block-header"><h3>3:1</h3></div>
  <div class="block-content">
<div class="grid grid-3-1">
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Nunc auctor luctus tellus a faucibus. Quisque dictum in eros sed consequat.</p>
<p>Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
</div>
  </div>
</div>

<div class="block">
  <div class="block-header"><h3>3:2</h3></div>
  <div class="block-content">
<div class="grid grid-3-2">
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Nunc auctor luctus tellus a faucibus. Quisque dictum in eros sed consequat.</p>
<p>Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
<div class="cell">
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
</div>
</div>
  </div>
</div>

</div>

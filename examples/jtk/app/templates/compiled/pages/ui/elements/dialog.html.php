<div class="block block-primary"><div class=" block-header"><div class=" block-toolbar">
<a data-close="dialog"><span class="icon"><?php echo $Icon->icon("close"); ?></span></a>
</div><h1>An ajax block</h1></div><?php $this->disableLayout(); ?><div class="block-content">


<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed pharetra lorem. Nunc auctor luctus tellus a faucibus. Quisque dictum in eros sed consequat.</p>
<p>Vestibulum consequat, ipsum at porttitor iaculis, nibh neque accumsan dui, sed sodales orci ligula eu mauris.</p>
<p><a data-open="dialog" data-modal title="Tooltip" class="<?php if ($this->isCurrent(array())) echo 'current'; ?>" href="<?php echo $this->link(array()); ?>">Open as modal</a></p>

</div><div class=" block-footer"><div>
<a data-close="dialog" class="button button-primary"><span class="icon"><?php echo $Icon->icon("checkmark"); ?></span>
<span class="label"><?php echo tr('OK'); ?></span>
</a>
</div></div></div>
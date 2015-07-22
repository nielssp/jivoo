<?php $this->view->data->title = tr('Icons'); ?>
 
<?php
$coreIcons = array(
  'checkmark', 'checkmark2', 'close', 'close2', 'star', 'star2',
  'disk', 'folder', 'folder-open', 'file',
  'arrow-up', 'arrow-down', 'arrow-left', 'arrow-right',
  'arrow-up-left', 'arrow-up-right', 'arrow-down-left', 'arrow-down-right'
);
$icons = array(
  'jivoo', 'home', 'home2', 'newspaper', 'pencil', 'pencil2', 'quill', 'pen', 'droplet', 'paint-format', 'image', 'image2', 'images', 'camera', 'music', 'film', 'camera2', 'connection', 'feed', 'book', 'books', 'file', 'profile', 'file2', 'file3', 'file4', 'copy', 'copy2', 'copy3', 'paste', 'paste2', 'paste3', 'stack', 'folder', 'folder-open', 'tag', 'tags', 'support', 'address-book', 'notebook', 'envelope', 'pushpin', 'location', 'location2', 'clock', 'clock2', 'alarm', 'alarm2', 'bell', 'stopwatch', 'calendar', 'calendar2', 'print', 'keyboard', 'screen', 'laptop', 'mobile', 'mobile2', 'tablet', 'cabinet', 'drawer', 'drawer2', 'drawer3', 'box-add', 'box-remove', 'download', 'upload', 'disk', 'storage', 'undo', 'redo', 'undo2', 'redo2', 'forward', 'reply', 'bubble', 'bubbles', 'bubbles2', 'bubble2', 'bubbles3', 'bubbles4', 'user', 'users', 'user2', 'users2', 'user3', 'user4', 'quotes-left', 'busy', 'binoculars', 'search', 'key', 'key2', 'lock', 'lock2', 'unlocked', 'wrench', 'settings', 'equalizer', 'cog', 'cogs', 'cog2', 'wand', 'bug', 'stats', 'mug', 'rocket', 'meter', 'dashboard', 'fire', 'lab', 'magnet', 'remove', 'remove2', 'briefcase', 'shield', 'lightning', 'switch', 'powercord', 'signup', 'list', 'list2', 'numbered-list', 'menu', 'menu2', 'tree', 'cloud', 'cloud-download', 'cloud-upload', 'download2', 'upload2', 'download3', 'upload3', 'globe', 'earth', 'link', 'flag', 'attachment', 'eye', 'eye-blocked', 'bookmark', 'bookmarks', 'star', 'star2', 'star3', 'heart', 'heart2', 'thumbs-up', 'thumbs-up2', 'warning', 'notification', 'question', 'info', 'close', 'checkmark', 'spell-check', 'plus', 'enter', 'exit', 'loop', 'arrow-up-left', 'arrow-up', 'arrow-up-right', 'arrow-right', 'arrow-down-right', 'arrow-down', 'arrow-down-left', 'arrow-left', 'arrow-up-left2', 'arrow-up2', 'arrow-up-right2', 'arrow-right2', 'arrow-down-right2', 'arrow-down2', 'arrow-down-left2', 'arrow-left2', 'embed', 'code', 'arrow-left3', 'arrow-down3', 'arrow-up3', 'arrow-right3', 'arrow-left4', 'arrow-down4', 'arrow-up4', 'arrow-right4'

);
?>

<style type="text/css">
.icon-table > .cell > .icon {
  font-size: 24px;
  height: 24px;
  width: 24px;
  margin: 12px 0;
}
</style>


<div class="block">
  <div class="block-header"><h2>Core icons <small><?php echo count($coreIcons); ?> Unicode icons</small></h2></div>
  <div class="block-content">
<div class="grid-auto-xs icon-table">
  <?php foreach ($coreIcons as $icon): ?>
  <div class="cell center">
    <div class="icon">
      <span class="jtk-icon-<?php echo $icon; ?>"></span>
    </div>
    <p><code><?php echo $icon; ?></code></p>
  </div>
  <?php endforeach; ?>
</div>
</div>
</div>

<div class="block">
  <div class="block-header"><h2>Theme icons <small><?php echo count($icons); ?> Icomoon icons</small></h2></div>
  <div class="block-content">
<div class="grid-auto-xs icon-table">
  <?php foreach ($icons as $icon): ?>
  <div class="cell center">
    <div class="icon"><?php echo $Icon->icon($icon); ?></div>
    <p><code><?php echo $icon; ?></code></p>
  </div>
  <?php endforeach; ?>
</div>
</div>
</div>

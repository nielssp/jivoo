<?php
ob_start();
?>
<p><?php echo tr('Welcome to PeanutCMS') ?><p>
<p><?php echo tr('This post indicates that PeanutCMS has been installed correctly, and is ready to be used.') ?></p>
<?php
return ob_get_clean(); 

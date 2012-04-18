<?php
require('../app/essentials.php');

// Make this shit work, please!
$core = new Core(array('http'));
$posts = $core->loadModule('posts');

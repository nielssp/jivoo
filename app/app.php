<?php
return array(
  'path' => str_replace('\\', '/', dirname(__FILE__)),
  'name' => 'PeanutCMS',
  'version' => '0.3.4',
  'defaultLanguage' => 'en',
  'minPhpVersion' => '5.2.0',
  'modules' => array(
    'ApakohPHP/Errors',
    'ApakohPHP/Shadow', 'ApakohPHP/Http',
    'ApakohPHP/Templates', 'ApakohPHP/Routes', 'ApakohPHP/Authentication',
    'ApakohPHP/Database', 'PeanutCMS/Theme', 'PeanutCMS/Backend',
    'PeanutCMS/Extensions', 'PeanutCMS/Posts', 'PeanutCMS/Links',
    'PeanutCMS/Pages'
  ),
);

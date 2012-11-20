<?php
return array(
  'path' => str_replace('\\', '/', dirname(__FILE__)),
  'name' => 'PeanutCMS',
  'version' => '0.3.4',
  'modules' => array(
    'Errors', 'Configuration', 'Shadow', 'I18n', 'Http', 'Templates',
    'Routes', 'Theme', 'Database', 'Authentication', 'Backend',
    'Extensions', 'Posts', 'Links', 'Pages'
  ),
);

<?php
return array(
  'path' => str_replace('\\', '/', dirname(__FILE__)),
  'name' => 'PeanutCMS',
  'version' => '0.3.4',
  'defaultLanguage' => 'en',
  'minPhpVersion' => '5.2.0',
  'sessionPrefix' => 'peanut_',
  'extensions' => array('Jquery', 'JqueryHotkeys', 'JqueryUi', 'Tinymce'),
  'modules' => array(
    'Core', 'PeanutCMS'
  ),
);

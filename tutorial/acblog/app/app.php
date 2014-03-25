<?php
return array(
  'path' => str_replace('\\', '/', dirname(__FILE__)),
  'name' => 'ACBlog',
  'version' => '0.0.1',
  'website' => 'http://apakoh.dk',
  'defaultLanguage' => 'en',
  'minPhpVersion' => '5.2.0',
  'sessionPrefix' => 'acblog_',
  'modules' => array(
    'Core',
    'Jivoo/Routing',
    'Jivoo/Controllers',
    'Jivoo/Templates',
  ),
);

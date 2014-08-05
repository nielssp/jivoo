<?php
return array(
  'path' => str_replace('\\', '/', dirname(__FILE__)),
  'name' => 'Jivoo ADK',
  'version' => '0.15-dev-5',
  'website' => 'http://jivoo.org',
  'defaultLanguage' => 'en',
  'minPhpVersion' => '5.2.0',
  'sessionPrefix' => 'jivoo_adk_',
  'import' => array(
    'Jivoo/Core',
    'Jivoo/Routing',
    'Jivoo/Assets',
    'Jivoo/Templates',
    'Jivoo/Controllers',
    'Jivoo/Setup',
    'Jivoo/Models', 
    'Jivoo/Editors',
    'Jivoo/Helpers',
    'Jivoo/AccessControl',
    'Jivoo/Administration',
    'Jivoo/Theme',
    'Jivoo/Widgets',
  ),
  'setup' => array(
    'Setup::Config::install'
  )
);

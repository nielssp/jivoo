<?php
return array(
  'name' => 'Jivoo',
  'version' => '0.15-dev-5',
  'website' => 'http://jivoo.org',
  'defaultLanguage' => 'en',
  'minPhpVersion' => '5.2.0',
  'sessionPrefix' => 'jivoo_',
  'extensions' => array('Jquery', 'JqueryHotkeys', 'JqueryUi', 'Tinymce', 'BasicWidgets', 'Html5shiv', 'Respond'),
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
    'Jivoo/Databases',
    'Jivoo/AccessControl',
    'Jivoo/Administration',
    'Jivoo/Theme',
    'Jivoo/Extensions',
    'Jivoo/Widgets',
  ),
  'setup' => array(
  	'Setup::Database::selectDriver',
  	'Setup::Database::setupDriver',
    'Setup::Auth::createUser',
  ),
  'listeners' => array(
    'PageRouting',
    'PostRouting',
    'AdminMenu',
  ),
  'defaultConfig' => array(
    'blog' => array(
      'fancyPermalinks' => true,
      'permalink' => '%year%/%month%/%name%',
      'comments' => array(
        'sorting' => 'desc',
        'childSorting' => 'asc',
        'display' => 'thread',
        'levelLimit' => 2,
        'editor' => array(
          'name' => 'HtmlEditor'
        ),
      ),
      'commentingDefault' => true,
      'anonymousCommenting' => false,
      'commentApproval' => false,
      'editor' => array('name' => 'TinymceEditor'),
    ),
  ),
);

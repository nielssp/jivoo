<?php
return array(
  'shadow' => array(
    'hashType' => 'sha512',
  ),
  'i18n' => array(
    'language' => 'en',
    'dateFormat' => 'Y-m-d',
    'timeFormat' => 'H:i:s',
    'timeZone' => 'Europe/Copenhagen',
  ),
  'language' => NULL,
  'http' => array(
    'rewrite' => 'off',
    'index' => array(
      'path' => 'posts',
      'query' => NULL,
    ),
  ),
  'site' => array(
    'title' => 'PeanutCMS',
    'subtitle' => 'The domesticated peanut is an amphidiploid or allotetraploid.',
    'meta' => NULL,
    'description' => NULL,
  ),
  'system' => array(
    'hide' => array(
      'identity' => 'off',
      'version' => 'off',
    ),
  ),
  'theme' => array(
    'name' => 'AwesomeAlien',
  ),
  'database' => array(
    'server' => 'localhost',
    'database' => 'peanutcms',
    'filename' => 'cfg/db.sqlite3',
    'driver' => 'PdoMysqlDatabase',
    'configured' => 'yes',
    'username' => 'peanutcms',
    'password' => 'peanutcms',
    'tablePrefix' => '',
    'migration' => array(
      'users' => '0.3.4',
      'groups' => '0.3.4',
      'groups_permissions' => '0.3.4',
      'posts' => '0.3.4',
      'tags' => '0.3.4',
      'posts_tags' => '0.3.4',
      'comments' => '0.3.4',
      'links' => '0.3.4',
      'pages' => '0.3.4',
    ),
  ),
  'authentication' => array(
    'defaultGroups' => array(
      'unregistered' => 'guests',
      'registered' => 'users',
    ),
    'rootCreated' => 'yes',
  ),
  'backend' => array(
    'path' => 'admin',
  ),
  'extensions' => array(
    'installed' => 'Tinymce Jquery JqueryUi JqueryHotkeys',
    'config' => array(
      'JqueryUi' => array(
        'theme' => 'arachis',
      ),
    ),
  ),
  'posts' => array(
    'fancyPermalinks' => 'on',
    'permalink' => '%year%/%month%/%name%',
    'comments' => array(
      'sorting' => 'desc',
      'childSorting' => 'asc',
      'display' => 'thread',
      'levelLimit' => '2',
      'editor' => array(
        'name' => 'HtmlEditor',
      ),
    ),
    'commentingDefault' => 'on',
    'anonymousCommenting' => 'off',
    'commentApproval' => 'off',
    'editor' => array(
      'name' => 'TinymceEditor',
    ),
  ),
  'pages' => array(
    'editor' => array(
      'name' => 'TinymceEditor',
    ),
  ),
);

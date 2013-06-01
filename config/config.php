<?php
return array(
  'Routing' => array(
    'rewrite' => false,
    'index' => array(
      'path' => 'posts',
      'query' => array(
      ),
    ),
    'sessionPrefix' => 'peanut_',
  ),
  'Assets' => array(
  ),
  'Templates' => array(
    'title' => 'PeanutCMS',
    'subtitle' => 'Powered by PeanutCMS',
  ),
  'Helpers' => array(
  ),
  'Controllers' => array(
  ),
  'Models' => array(
  ),
  'Shadow' => array(
    'hashType' => 'sha512',
  ),
  'Editors' => array(
  ),
  'Database' => array(
    'server' => 'localhost',
    'database' => 'peanutcms',
    'filename' => '/home/www/GOTUN/PeanutCMS/config/db.sqlite3',
    'driver' => 'PdoMysql',
    'configured' => 'yes',
    'username' => 'peanutcms',
    'password' => 'peanutcms',
    'tablePrefix' => '',
    'migration' => array(
      'users' => '0.3.4',
      'groups' => '0.3.4',
      'groups_permissions' => '0.3.4',
      'links' => '0.3.4',
      'pages' => '0.3.4',
      'posts' => '0.3.4',
      'tags' => '0.3.4',
      'posts_tags' => '0.3.4',
      'comments' => '0.3.4',
    ),
  ),
  'Authentication' => array(
    'defaultGroups' => array(
      'unregistered' => 'guests',
      'registered' => 'users',
    ),
    'rootCreated' => array(
    ),
  ),
  'Core' => array(
  ),
  'Backend' => array(
    'path' => 'admin',
  ),
  'Extensions' => array(
    'config' => array(
    ),
    'installed' => '',
  ),
  'Links' => array(
  ),
  'Pages' => array(
    'editor' => array(
      'name' => 'TinymceEditor',
    ),
  ),
  'Posts' => array(
    'fancyPermalinks' => true,
    'permalink' => '%year%/%month%/%name%',
    'comments' => array(
      'sorting' => 'desc',
      'childSorting' => 'asc',
      'display' => 'thread',
      'levelLimit' => 2,
      'editor' => array(
        'name' => 'HtmlEditor',
      ),
    ),
    'commentingDefault' => true,
    'anonymousCommenting' => false,
    'commentApproval' => false,
    'editor' => array(
      'name' => 'TinymceEditor',
    ),
  ),
  'Theme' => array(
    'name' => 'AwesomeAlien',
  ),
  'PeanutCMS' => array(
  ),
  'logging' => array(
    'level' => 15,
  ),
  'Maintenance' => array(
    'showErrorReport' => false,
  ),
  'core' => array(
    'language' => 'en',
    'timeZone' => 'UTC',
  ),
);

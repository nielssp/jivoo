<?php
require('../app/essentials.php');

require('../app/classes/db-drivers/mysql.class.php');

$db = Mysql::connect('localhost', 'peanutcms', 'peanutcms', 'peanutcms-testing');

ActiveRecord::connect($db);

ActiveRecord::addModel('Post', 'posts');

class Post extends ActiveRecord {
}

$posts = Post::all(
  SelectQuery::create()
    ->where('title LIKE ?')
    ->addVar('%hello%')
);

foreach ($posts as $post) {
  echo $post->id . ': ' . $post->title . '<br/>';
}
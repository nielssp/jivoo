<?php
include '../app/essentials.php';

include p(CLASSES . 'database/MysqlDatabase.php');

echo '<pre>';


$options = array(
  'server' => 'localhost',
  'username' => 'peanutcms-test',
  'password' => 'peanutcms-test',
  'database' => 'peanutcms-testing'
);

$db = new MysqlDatabase($options);

$db->migrate(new postsSchema());
$db->migrate(new tagsSchema());
$db->migrate(new posts_tagsSchema());

Post::connect($db->posts);
Tag::connect($db->tags);

$post = Post::find(1);
$tag = Tag::find(1);

var_dump($post->title);
var_dump($tag->name);


echo '</pre>';

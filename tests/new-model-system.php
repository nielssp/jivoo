<?php
include '../lib/Core/bootstrap.php';

Lib::import('Core');
Lib::import('Core/Routing');
Lib::import('Core/Models');
Lib::import('Core/Database');
Lib::import('Core/Database/PdoMysql');
Lib::addIncludePath('../app/models');

$db = new PdoMysqlDatabase(array(
  'server' => 'localhost',
  'username' => 'peanutcms',
  'password' => 'peanutcms',
  'database' => 'peanutcms',
));

$sources = new Dictionary();

$sources->posts_tags = $db->posts_tags;

$models = new Dictionary();
$models->User = new ActiveModel('User', $db->users, $models, $sources);
$models->Post = new ActiveModel('Post', $db->posts, $models, $sources);
$models->Group = new ActiveModel('Group', $db->groups, $models, $sources);
$models->Link = new ActiveModel('Link', $db->links, $models, $sources);
$models->Tag = new ActiveModel('Tag', $db->tags, $models, $sources);
$models->Comment = new ActiveModel('Comment', $db->comments, $models, $sources);

$post = $models->Post->findById(11);
$post = $post[0];

header('Content-Type:text/plain');


$tags = $post->getTags();
var_dump(count($tags));
$tag = $models->Tag->create();
$tag->tag = 'test27';
$tag->name = 'Test27';
var_dump($tag->save());
$tag->addPost($post);
var_dump($post->countTags());
$comments = $post->getComments();
var_dump($post->hasComment($comments[0]));
var_dump($post->hasTag($tags[0]));
var_dump($comments[0]->getPost()->title);

var_dump(Logger::getLog());

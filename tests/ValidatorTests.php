<?php
define('ALLOW_REDIRECT', FALSE);
include '../app/essentials.php';

$core = new Core();
$core->loadModule('Posts');

$validator = Comment::getModelValidator();

$comment = Comment::create();

$comment->isValid();

var_dump($comment->getErrors());
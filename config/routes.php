<?php
return array(
  'posts' => 'Posts/index',
  'posts/*' => 'Posts/view',
  'tags' => 'Posts/tagIndex',
  'tags/*' => 'Posts/viewTag',
  'posts/*/comments' => 'Comments/index',
  'posts/*/comments/*' => 'Comments/view',
);

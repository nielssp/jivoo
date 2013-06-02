<?php
// Module
// Name           : Posts
// Version        : 0.3.0
// Description    : The PeanutCMS blogging system
// Author         : PeanutCMS
// Dependencies   : Core/Database Core/Routing Core/Templates
//                  Core/Authentication PeanutCMS/Backend
//                  Core/Controllers

/*
 * Class for working with blog posts
 *
 * @package PeanutCMS
 */

/**
 * Posts class
 */
class Posts extends ModuleBase {
  private $posts;
  private $comments;

  protected function init() {

    // Set default settings
    $this->config->defaults = array(
      'fancyPermalinks' => true,
      'permalink' => '%year%/%month%/%name%',
      'comments' => array(
        'sorting' => 'desc',
        'childSorting' => 'asc',
        'display' => 'thread',
        'levelLimit' => 2,
        'editor' => array('name' => 'HtmlEditor'),
      ),
      'commentingDefault' => true,
      'anonymousCommenting' => false,
      'commentApproval' => false,
      'editor' => array('name' => 'TinymceEditor'),
    );

    // Set up models
    $newInstall = false;

    $postsSchema = new postsSchema();
    $tagsSchema = new tagsSchema();
    $posts_tagsSchema = new posts_tagsSchema();
    $commentsSchema = new commentsSchema();

    $newInstall = $this->m
      ->Database
      ->migrate($postsSchema) == 'new';
    $this->m
      ->Database
      ->migrate($tagsSchema);
    $this->m
      ->Database
      ->migrate($posts_tagsSchema);
    $this->m
      ->Database
      ->migrate($commentsSchema);

    $this->m
      ->Database
      ->posts
      ->setSchema($postsSchema);
    $this->m
      ->Database
      ->tags
      ->setSchema($tagsSchema);
    $this->m
      ->Database
      ->posts_tags
      ->setSchema($posts_tagsSchema);
    $this->m
      ->Database
      ->comments
      ->setSchema($commentsSchema);

    Post::connect($this->m
      ->Database
      ->posts);
    Tag::connect($this->m
      ->Database
      ->tags);
    Comment::connect($this->m
      ->Database
      ->comments);

    if ($this->config['anonymousCommenting'] == 'on') {
      Comment::setAnonymousCommenting(true);
    }

    if ($newInstall) {
      $post = Post::create();
      $post->title = tr('Welcome to PeanutCMS');
      $post->name = 'welcome-to-peanutcms';
      $post->content = include $this->p('welcomePost.php');
      $post->date = time();
      $post->comments = 0;
      $post->status = 'published';
      $post->commenting = 'yes';
      $post->save();
      $comment = Comment::create();
      $comment->author = 'PeanutCMS';
      $comment->content = 'Welcome to PeanutCMS.';
      $comment->date = time();
      $comment->setPost($post);
      $comment->save();
    }

    // Encoder
    $commentEncoder = new Encoder();
    $commentEncoder->allowTag('strong');
    $commentEncoder->allowTag('br');
    $commentEncoder->allowTag('p');
    $commentEncoder->allowTag('a');
    $commentEncoder->allowAttribute('a', 'href');
    $commentEncoder->validateAttribute('a', 'href', 'url', true);
    $commentEncoder->appendAttributes('a', 'rel="nofollow"');
    $commentEncoder->allowTag('img');
    $commentEncoder->allowAttribute('img', 'src');
    $commentEncoder->validateAttribute('img', 'src', 'url', true);
    Comment::setEncoder('content', $commentEncoder);

    $postsEncoder = new Encoder();
    $postsEncoder->setAllowAll(true);
    Post::setEncoder('content', $postsEncoder);

    // Create controllers
//     $this->posts = new PostsController($this->m->Routing, $this->config);
    $this->posts = $this->m->Controllers->Posts;
    $this->posts->setConfig($this->config);

    $this->comments = $this->m->Controllers->Comments;
    $this->comments->setConfig($this->config['comments']);

    // Frontend setup

    $this->posts->addRoute('posts', 'index');

    $this->posts->addRoute('tags', 'tagIndex');
    $this->posts->addRoute('tags/*', 'viewTag');

    if ($this->config['fancyPermalinks']) {
      // Detect fancy post permalinks
      $this->detectFancyPath();
      $this->m->Routing->addPath('Posts', 'view', array($this, 'getFancyPath'));
      $this->m->Routing
        ->addPath('Comments', 'index', array($this, 'getFancyPath'));
      $this->m->Routing
        ->addPath('Comments', 'view', array($this, 'getFancyPath'));
    }
    else {
      $this->posts->addRoute('posts/*', 'view');
      $this->comments->addRoute('posts/*/comments', 'index');
      $this->comments->addRoute('posts/*/comments/*', 'view');
    }

    // Backend setup

    $this->m->Backend['content']->setup(tr('Content'), 2);
    $this->m->Backend['content']['posts-add']->setup(tr('New post'), 2)
      ->permission('backend.posts.add')->autoRoute($this->posts, 'add');
    $this->m->Backend['content']['posts-manage']->setup(tr('Manage posts'), 4)
      ->permission('backend.posts.manage')->autoRoute($this->posts, 'manage');

    $this->m->Backend['content']['comments']->setup(tr('Comments'), 8)
      ->permission('backend.comments.manage')
      ->autoRoute($this->comments, 'manage');
    $this->m->Backend['content']['tags']->setup(tr('Tags'), 8)
      ->permission('backend.tags.manage')
      ->autoRoute($this->posts, 'manageTags');

    $this->m->Backend->unlisted['posts-edit']->permission('backend.posts.edit')
      ->autoRoute($this->posts, 'edit');
    $this->m->Backend->unlisted['posts-delete']
      ->permission('backend.posts.delete')->autoRoute($this->posts, 'delete');
    $this->m->Backend->unlisted['comment.delete']
      ->permission('backend.comments.delete')
      ->autoRoute($this->comments, 'delete');
    $this->m->Backend->unlisted['comments-edit']
      ->permission('backend.comments.edit')->autoRoute($this->comments, 'edit');
    //$this->m->Backend->addPage('content', 'manage-posts', tr('Manage Posts'), array($this, 'newPostController'), 4);
    //$this->m->Backend->addPage('content', 'tags', tr('Tags'), array($this, 'newPostController'), 8);
    //$this->m->Backend->addPage('content', 'categories', tr('Categories'), array($this, 'newPostController'), 8);
  }

  private function detectFancyPath() {
    $path = $this->request->path;
    $permalink = explode('/', $this->config['permalink']);
    if (!is_array($path) OR !is_array($permalink)) {
      return;
    }
    if (count($path) != count($permalink)) {
      return;
    }
    $name = '';
    $id = 0;
    foreach ($permalink as $key => $dir) {
      if (empty($path[$key])) {
        return;
      }
      switch ($dir) {
        case '%year%':
          if (preg_match('/^[0-9]{4}$/', $path[$key]) !== 1) {
            return;
          }
          break;
        case '%month%':
          if (preg_match('/^[0-9]{2}$/', $path[$key]) !== 1) {
            return;
          }
          break;
        case '%day%':
          if (preg_match('/^[0-9]{2}$/', $path[$key]) !== 1) {
            return;
          }
          break;
        case '%name%':
          $name = $path[$key];
          break;
        case '%id%':
          if (preg_match('/^[0-9]+$/', $path[$key]) !== 1) {
            return;
          }
          $id = $path[$key];
          break;
        default:
          if ($dir != $path[$key]) {
            return;
          }
          break;
      }
    }
    if ($id > 0) {
      $post = Post::find($id);
      if ($post !== false) {
        $post->addToCache();
        $this->posts
          ->setRoute('view', 6, array($post->id));
        return;
      }
    }
    else if (!empty($name)) {
      $post = Post::first(
        SelectQuery::create()->where('name = ?')
          ->addVar($name));
      if ($post !== false) {
        $post->addToCache();
        $this->posts
          ->setRoute('view', 6, array($post->id));
        return;
      }
    }
  }

  public function getFancyPath($parameters) {
    $permalink = explode('/', $this->config['permalink']);
    if (is_array($permalink)) {
      if (is_object($parameters) AND is_a($parameters, 'Post')) {
        $record = $parameters;
      }
      else {
        if ($parameters[0] == 0) {
          $record = Post::create();
          $record->name = '%name%';
          $record->date = time();
        }
        else {
          $record = Post::find($parameters[0]);
        }
      }
      $time = $record->date;
      $replace = array('%name%' => $record->name,
        '%id%' => (isset($record->id)) ? $record->id : 0,
        '%year%' => tdate('Y', $time), '%month%' => tdate('m', $time),
        '%day%' => tdate('d', $time)
      );
      $search = array_keys($replace);
      $replace = array_values($replace);
      $path = array();
      foreach ($permalink as $dir) {
        $path[] = str_replace($search, $replace, $dir);
      }
      return $path;
    }
    return false;
  }
}

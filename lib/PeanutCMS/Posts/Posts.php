<?php
// Module
// Name           : Posts
// Description    : The PeanutCMS blogging system
// Author         : apakoh.dk
// Dependencies   : Core/Database Core/Routing Core/Templates
//                  Core/Authentication PeanutCMS/Backend
//                  Core/Controllers Core/Models PeanutCMS/Widgets

/**
 * Class for working with blog posts
 * @package PeanutCMS\Posts
 */
class Posts extends ModuleBase {

  protected function init() {

    // Set default settings
    $this->config->defaults = array('fancyPermalinks' => true,
      'permalink' => '%year%/%month%/%name%',
      'comments' => array('sorting' => 'desc', 'childSorting' => 'asc',
        'display' => 'thread', 'levelLimit' => 2,
        'editor' => array('name' => 'HtmlEditor'),
      ), 'commentingDefault' => true, 'anonymousCommenting' => false,
      'commentApproval' => false, 'editor' => array('name' => 'TinymceEditor'),
    );

    $this->m->Widgets->register(new RecentPostsWidget(
      $this->m->Routing,
      $this->p('templates/recent-posts-widget.html.php')
    ));

    if ($this->m->Database->isNew('posts')) {
      $post = $this->m->Models->Post->create();
      $post->title = tr('Welcome to PeanutCMS');
      $post->name = 'welcome-to-peanutcms';
      $post->content = include $this->p('welcomePost.php');
      $post->date = time();
      $post->comments = 0;
      $post->status = 'published';
      $post->commenting = 'yes';
      $post->save();
      $comment = $this->m->Models->Comment->create();
      $comment->author = 'PeanutCMS';
      $comment->content = 'Welcome to PeanutCMS.';
      $comment->date = time();
      $comment->setPost($post);
      $comment->save();
    }

    $commentValidator = $this->m->Models->Comment->validator;
    if ($this->config['anonymousCommenting']) {
      unset($commentValidator->author->presence);
      unset($commentValidator->email->presence);
    }
    else {
      $commentValidator->author->presence = true;
      $commentValidator->email->presence = true;
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
    $this->m->Models->Comment->setEncoder('content', $commentEncoder);

    $postsEncoder = new Encoder();
    $postsEncoder->setAllowAll(true);
    $this->m->Models->Post->setEncoder('content', $postsEncoder);

    // Create controllers
    //     $this->posts = new PostsController($this->m->Routing, $this->config);

//     $this->posts->setConfig($this->config);

//     $this->comments->setConfig($this->config['comments']);

    // Frontend setup

    $this->m->Routing->addRoute('posts', 'Posts::index');

    $this->m->Routing->addRoute('tags', 'Posts::tagIndex');
    $this->m->Routing->addRoute('tags/*', 'Posts::viewTag');

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
      $this->m->Routing->addRoute('posts/*', 'Posts::view');
      $this->m->Routing->addRoute('posts/*/comments', 'Posts::index');
      $this->m->Routing->addRoute('posts/*/comments/*', 'Posts::view');
    }

    // Backend setup
    
    $this->m->Routing->autoRoute('PostsBackend');
    $this->m->Routing->autoRoute('CommentsBackend');
    $this->m->Routing->autoRoute('TagsBackend');

    $this->m->Backend['content']->setup(tr('Content'), 2)
      ->item(tr('New post'), 'Backend::Posts::add', 2, 'backend.posts.add')
      ->item(tr('Manage posts'), 'Backend::Posts', 4, 'backend.posts.index')
      ->item(tr('Comments'), 'Backend::Comments', 8, 'backend.comments.index')
      ->item(tr('Tags'), 'Backend::Tags', 8, 'backend.tags.index');

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
      $post = $this->m->Models->Post->find($id);
      if ($post !== false) {
        $post->addToCache();
        $this->posts->setRoute('view', 6, array($post->id));
        return;
      }
    }
    else if (!empty($name)) {
      $post = $this->m->Models->Post->first(
        SelectQuery::create()->where('name = ?')->addVar($name)
      );
      if ($post !== false) {
        $post->addToCache();
        $this->m->Routing->setRoute(array(
          'controller' => 'Posts',
          'action' => 'view',
          'parameters' => array($post->id)
          ), 6
        );
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
          $record = $this->m->Models->Post->create();
          $record->name = '%name%';
          $record->date = time();
        }
        else {
          $record = $this->m->Models->Post->find($parameters[0]);
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

<?php
// Module
// Name           : Posts
// Version        : 0.2.0
// Description    : The PeanutCMS blogging system
// Author         : PeanutCMS
// Dependencies   : errors configuration database routes templates http
//                  users backend

/*
 * Class for working with blog posts
 *
 * @package PeanutCMS
 */

/**
 * Posts class
 */
class Posts implements IModule{

  private $core;
  private $errors;
  private $configuration;
  private $database;
  private $routes;
  private $templates;
  private $http;
  private $backend;
  private $users;

  private $post;

  public function __construct(Core $core) {
    $this->core = $core;
    $this->database = $this->core->database;
    $this->actions = $this->core->actions;
    $this->routes = $this->core->routes;
    $this->http = $this->core->http;
    $this->templates = $this->core->templates;
    $this->errors = $this->core->errors;
    $this->configuration = $this->core->configuration;
    $this->users = $this->core->users;
    $this->backend = $this->core->backend;

    $newInstall = FALSE;

    require_once(p(MODELS . 'post.class.php'));

    if (!$this->database->tableExists('posts')) {
      $this->database->createQuery('posts')
        ->addInt('id', TRUE, TRUE)
        ->setPrimaryKey('id')
        ->addVarchar('name', 255)
        ->addVarchar('title', 255)
        ->addText('content')
        ->addInt('date', TRUE)
        ->addInt('comments', TRUE)
        ->addVarchar('state', 10)
        ->addVarchar('commenting', 10)
        ->addIndex(TRUE, 'name')
        ->addIndex(FALSE, 'date')
        ->execute();
      $newInstall = TRUE;
    }

    ActiveRecord::addModel('Post', 'posts');

    require_once(p(MODELS . 'tag.class.php'));

    if (!$this->database->tableExists('tags')) {
      $this->database->createQuery('tags')
        ->addInt('id', TRUE, TRUE)
        ->setPrimaryKey('id')
        ->addVarchar('tag', 255)
        ->addVarchar('name', 255)
        ->addIndex(TRUE, 'name')
        ->execute();
    }
    if (!$this->database->tableExists('posts_tags')) {
      $this->database->createQuery('posts_tags')
        ->addInt('post_id', TRUE)
        ->addInt('tag_id', TRUE)
        ->setPrimaryKey('post_id', 'tag_id')
        ->execute();
    }

    ActiveRecord::addModel('Tag', 'tags');

    require_once(p(MODELS . 'comment.class.php'));

    if (!$this->database->tableExists('comments')) {
      $this->database->createQuery('comments')
        ->addInt('id', TRUE, TRUE)
        ->setPrimaryKey('id')
        ->addInt('post_id', TRUE)
        ->addInt('user_id', TRUE, FALSE, 0)
        ->addInt('parent_id', TRUE, FALSE, 0)
        ->addVarchar('author', 255)
        ->addVarchar('email', 255)
        ->addVarchar('website', 255)
        ->addText('content')
        ->addInt('date', TRUE)
        ->addIndex(FALSE, 'post_id')
        ->execute();
    }

    ActiveRecord::addModel('Comment', 'comments');

    if ($newInstall) {
      $post = Post::create();
      $post->title = 'Welcome to PeanutCMS';
      $post->name = 'welcome-to-peanutcms';
      $post->content = '<p>Welcome to PeanutCMS.</p>';
      $post->date = time();
      $post->comments = 0;
      $post->state = 'published';
      $post->commenting = 'on';
      $post->save();
      $comment = Comment::create();
      $comment->author = 'PeanutCMS';
      $comment->content = 'Welcome to PeanutCMS.';
      $comment->date = time();
      $comment->setPost($post);
      $comment->save();
    }

    // Set default settings
    if (!$this->configuration->exists('posts.fancyPermalinks')) {
      $this->configuration->set('posts.fancyPermalinks', 'on');
    }
    if (!$this->configuration->exists('posts.permalink')) {
      $this->configuration->set('posts.permalink', '%year%/%month%/%name%');
    }
    if (!$this->configuration->exists('posts.comments.sorting')) {
      $this->configuration->set('posts.comments.sorting', 'desc');
    }
    if (!$this->configuration->exists('posts.comments.childSorting')) {
      $this->configuration->set('posts.comments.childSorting', 'asc');
    }
    if (!$this->configuration->exists('posts.comments.display')) {
      $this->configuration->set('posts.comments.display', 'thread');
    }
    if (!$this->configuration->exists('posts.comments.levelLimit')) {
      $this->configuration->set('posts.comments.levelLimit', '2');
    }
    if (!$this->configuration->exists('posts.commentingDefault')) {
      $this->configuration->set('posts.commentingDefault', 'on');
    }
    if (!$this->configuration->exists('posts.anonymousCommenting')) {
      $this->configuration->set('posts.anonymousCommenting', 'off');
    }
    if (!$this->configuration->exists('posts.commentApproval')) {
      $this->configuration->set('posts.commentApproval', 'off');
    }

    if ($this->configuration->get('posts.fancyPermalinks') == 'on') {
      // Detect fancy post permalinks
      $this->detectFancyPermalinks();
    }
    else {
      $this->routes->addRoute('posts/*', array($this, 'postController'));
      //$this->routes->addRoute('posts/*/comments', array($this, ''));
      //$this->routes->addRoute('posts/*/comments/*', array($this, ''));
    }
    $this->routes->addRoute('posts', array($this, 'postListController'));
    //$this->routes->addRoute('tags', array($this, ''));
    //$this->routes->addRoute('tags/*', array($this, ''));
    
    $this->backend->addCategory('content', tr('Content'), 2);
    $this->backend->addPage('content', 'new-post', tr('New Post'), array($this, 'newPostController'), 2);
    $this->backend->addPage('content', 'manage-posts', tr('Manage Posts'), array($this, 'newPostController'), 4);
    $this->backend->addPage('content', 'tags', tr('Tags'), array($this, 'newPostController'), 8);
    $this->backend->addPage('content', 'categories', tr('Categories'), array($this, 'newPostController'), 8);
  }

  private function detectFancyPermalinks() {
    $path = $this->routes->getPath();
    $permalink = explode('/', $this->configuration->get('posts.permalink'));
    if (is_array($path) AND is_array($permalink)) {
      foreach ($permalink as $key => $dir) {
        if (isset($path[$key])) {
          $pos = strpos($dir, '%name%');
          $len = strlen($dir);
          if ($pos !== false) {
            $dif = $len - ($pos + 6);
            if ($dif != 0) {
              $name = substr($path[$key], $pos, -$dif);
            }
            else {
              $name = substr($path[$key], $pos);
            }
            if (!empty($name)) {
              $post = Post::first(
                SelectQuery::create()
                  ->where('name = ?')
                  ->addVar($name)
              );
              if ($post !== FALSE) {
                $perma = $post->getPath();
                if ($perma !== false) {
                  if ($perma == $path) {
                    $post->addToCache();
                    $this->post = $post->id;
                    $this->routes->setRoute(array($this, 'postController'), 6);
                    return;
                  }
                }
              }
            }
          }
          $pos = strpos($dir, '%id%');
          $len = strlen($dir);
          if ($pos !== FALSE) {
            $dif = $len - ($pos + 4);
            if ($dif != 0) {
              $postid = substr($path[$key], $pos, -$dif);
            }
            else {
              $postid = substr($path[$key], $pos);
            }
            $post = Post::find($postid);
            if ($post !== FALSE) {
              $perma = $post->getPath();
              if ($perma !== false) {
                if ($perma == $path) {
                  $post->addToCache();
                  $this->post = $post->id;
                  $this->routes->setRoute(array($this, 'postController'), 6);
                  return;
                }
              }
            }
          }
        }
      }
      foreach ($path as $name) {
        if (!empty($name)) {
          $post = Post::first(
            SelectQuery::create()
              ->where('name = ?')
              ->addVar($name)
          );
          if ($post !== FALSE) {
            $post->addToCache();
            $this->post = $post->id;
            $this->routes->setRoute(array($this, 'postController'), 3);
          }
        }
      }
    }
  }

  public function getPath(ActiveRecord $record) {
    $class = get_class($record);
    switch ($class) {
      case 'Post':
        if ($this->configuration->get('posts.fancyPermalinks') == 'on') {
          $permalink = explode('/', $this->configuration->get('posts.permalink'));
          if (is_array($permalink)) {
            $time = $record->date;
            $id = $record->id;
            $id = !isset($id) ? 0 : $record->id;
            $replace = array('%name%'  => $record->name,
                             '%id%'    => $id,
                             '%year%'  => date('Y', $time),
                             '%month%' => date('m', $time),
                             '%day%'   => date('d', $time));
            $search = array_keys($replace);
            $replace = array_values($replace);
            $path = array();
            foreach ($permalink as $dir) {
              $path[] = str_replace($search, $replace, $dir);
            }
            return $path;
          }
        }
        else {
          return array('posts', $record->id);
        }
        break;
      case 'Tag':
        return array('tags', $record->name);
        break;
      case 'Comment':
        return array_merge(
          $this->getPath(Post::find($record->post_id)),
          array('comments', $record->id)
        );
        break;
      default:
        return false;
    }
  }

  public function getLink(ILinkable $record) {
    return $this->http->getLink($this->getPath($record));
  }


  public function postListController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();

    $templateData['posts'] = Post::all(
      SelectQuery::create()
        ->orderByDescending('date')
        ->limit(5)
    );

    $this->templates->renderTemplate('list-posts.html', $templateData);
  }

  public function postController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();

    if ($this->configuration->get('posts.fancyPermalinks') == 'on') {
      $templateData['post'] = Post::find($this->post);
    }
    else {
      $templateData['post'] = Post::find((int) $path[1]);
    }

    if (!$this->http->isCurrent($templateData['post']->getPath())) {
      $this->http->redirectPath($templateData['post']->getPath());
    }

    $templateData['title'] = $templateData['post']->title;

    $templateData['comments'] = array();

    /**
     * Just testing...
     * @todo JSON interface/whatever...
     */
    if (isset($parameters['json'])) {
      header('Content-Type: application/json;charset=utf-8');
      echo $templateData['post']->json();
    }
    else {
      $this->templates->renderTemplate('post.html', $templateData);
    }
  }


  public function newPostController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();
    $templateData['title'] = tr('New Post');
    $templateData['action'] = $this->http->getLink();

    $examplePost = Post::create();
    $examplePost->name = '%name%';
    $examplePost->date = time();
    $exampleLink = explode('%name%', $examplePost->getLink());
    $examplePost = NULL;
    $templateData['nameInPermalink'] = count($exampleLink) >= 2;
    $templateData['beforePermalink'] = $exampleLink[0];
    $templateData['afterPermalink'] = $exampleLink[1];

    $templateData['values'] = array();
    $templateData['values']['title'] = '';
    $templateData['values']['content'] = '';
    $templateData['values']['tags'] = '';
    $templateData['values']['permalink'] = '';
    $templateData['values']['allow_comments'] = TRUE;
    if (isset($_POST['save'])) {
      $this->templates->insertHtml('message', 'body-bottom', 'div', array(), 'Saving...');
      $templateData['values']['title'] = $_POST['title'];
      $templateData['values']['content'] = $_POST['content'];
      $templateData['values']['tags'] = $_POST['tags'];
    }
    else if (isset($_POST['publish'])) {
      $this->templates->insertHtml('message', 'body-bottom', 'div', array(), 'Publishing...');
    }
    $this->templates->renderTemplate('backend/edit-post.html', $templateData);
  }
}

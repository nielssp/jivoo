<?php
/*
 * Class for working with blog posts
 *
 * @package PeanutCMS
 */

/**
 * Posts class
 */
class Posts implements IModule{

  private $errors;
  private $configuration;
  private $database;
  private $routes;
  private $templates;
  private $http;
  private $backend;
  private $users;

  public function getConfiguration() {
    return $this->configuration;
  }

  public function getErrors() {
    return $this->errors;
  }

  public function getHttp() {
    return $this->http;
  }

  public function getUsers() {
    return $this->users;
  }

  public function getBackend() {
    return $this->backend;
  }

  public function getDatabase() {
    return $this->database;
  }

  public function getRoutes() {
    return $this->routes;
  }

  public function getTemplates() {
    return $this->templates;
  }

  private $post;

  public function __construct(Backend $backend) {
    $this->backend = $backend;
    $this->users = $backend->getUsers();
    $this->database = $backend->getDatabase();
    $this->routes = $this->database->getRoutes();
    $this->http = $this->routes->getHttp();
    $this->templates = $this->routes->getTemplates();
    $this->errors = $this->routes->getErrors();
    $this->configuration = $this->database->getConfiguration();

    if (!ActiveRecord::isConnected()) {
      throw new Exception('temporary.');
    }

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
    if (!$this->configuration->exists('posts.comments.orting')) {
      $this->configuration->set('posts.comments.orting', 'desc');
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
  }

  public static function getDependencies() {
    return array('backend');
  }

  private function detectFancyPermalinks() {
    $path = $this->routes->getPath();
    $permalink = explode('|', $this->configuration->get('postPermalink'));
    if (is_array($path)) {
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
          if ($pos !== false) {
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
            $replace = array('%name%'  => $record->name,
                                       '%id%'    => $record->id,
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


  public function postListController($parameters = array(), $contentType = 'html') {
    $templateData = array();

    $templateData['posts'] = Post::all(
      SelectQuery::create()
        ->orderByDescending('date')
        ->limit(5)
    );

    $this->templates->renderTemplate('list-posts.html', $templateData);
  }

  public function postController($parameters = array(), $contentType = 'html') {
    $templateData = array();
    $path = $this->routes->getPath();

    if ($this->configuration->get('posts.fancyPermalinks') == 'on') {
      $templateData['post'] = Post::find($this->post);
    }
    else {
      $templateData['post'] = Post::find((int) $path[1]);
    }

    if ($templateData['post']->getPath() != $path) {
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
}

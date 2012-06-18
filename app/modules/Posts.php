<?php
// Module
// Name           : Posts
// Version        : 0.3.0
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
class Posts extends ModuleBase {
  private $post;
  
  private $controller;

  protected function init() {

    $newInstall = FALSE;

    require_once(p(MODELS . 'Post.php'));

    if (!$this->m->Database->tableExists('posts')) {
      $this->m->Database->createQuery('posts')
        ->addInt('id', TRUE, TRUE)
        ->setPrimaryKey('id')
        ->addVarchar('name', 255)
        ->addVarchar('title', 255)
        ->addText('content')
        ->addInt('date', TRUE)
        ->addInt('comments', TRUE)
        ->addVarchar('state', 10)
        ->addVarchar('commenting', 10)
        ->addInt('user_id', TRUE)
        ->addIndex(TRUE, 'name')
        ->addIndex(FALSE, 'date')
        ->addIndex(FALSE, 'user_id')
        ->execute();
      $newInstall = TRUE;
    }

    ActiveRecord::addModel('Post', 'posts');

    require_once(p(MODELS . 'Tag.php'));

    if (!$this->m->Database->tableExists('tags')) {
      $this->m->Database->createQuery('tags')
        ->addInt('id', TRUE, TRUE)
        ->setPrimaryKey('id')
        ->addVarchar('tag', 255)
        ->addVarchar('name', 255)
        ->addIndex(TRUE, 'name')
        ->execute();
    }
    if (!$this->m->Database->tableExists('posts_tags')) {
      $this->m->Database->createQuery('posts_tags')
        ->addInt('post_id', TRUE)
        ->addInt('tag_id', TRUE)
        ->setPrimaryKey('post_id', 'tag_id')
        ->execute();
    }

    ActiveRecord::addModel('Tag', 'tags');

    require_once(p(MODELS . 'Comment.php'));

    if (!$this->m->Database->tableExists('comments')) {
      $this->m->Database->createQuery('comments')
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
    $this->m->Configuration->setDefault(array(
      'posts.fancyPermalinks' => 'on',
      'posts.permalink' => '%year%/%month%/%name%',
      'posts.comments.sorting' => 'desc',
      'posts.comments.childSorting' => 'asc',
      'posts.comments.display' => 'thread',
      'posts.comments.levelLimit' => '2',
      'posts.commentingDefault' => 'on',
      'posts.anonymousCommenting' => 'off',
      'posts.commentApproval' => 'off'
    ));
    
    // Create controller
    $this->controller = new PostsController($this->m->Templates, $this->m->Routes);

    $this->controller->addRoute('posts', 'index');

    if ($this->m->Configuration->get('posts.fancyPermalinks') == 'on') {
      // Detect fancy post permalinks
      $this->detectFancyPermalinks();
      $this->m->Routes->addPath('Posts', 'view', array($this, 'getFancyPath'));
    }
    else {
      $this->m->Routes->addRoute('posts/*', array($this, 'postController'));
      //$this->routes->addRoute('posts/*/comments', array($this, ''));
      //$this->routes->addRoute('posts/*/comments/*', array($this, ''));
    }
    //$this->m->Routes->addRoute('posts', array($this, 'postListController'));
    //$this->routes->addRoute('tags', array($this, ''));
    //$this->routes->addRoute('tags/*', array($this, ''));
    
    $this->m->Backend->addCategory('content', tr('Content'), 2);
    $this->m->Backend->addPage('content', 'new-post', tr('New Post'), array($this, 'newPostController'), 2);
    $this->m->Backend->addPage('content', 'manage-posts', tr('Manage Posts'), array($this, 'newPostController'), 4);
    $this->m->Backend->addPage('content', 'tags', tr('Tags'), array($this, 'newPostController'), 8);
    $this->m->Backend->addPage('content', 'categories', tr('Categories'), array($this, 'newPostController'), 8);
  }

  private function detectFancyPermalinks() {
    $path = $this->m->Http->getPath();
    $permalink = explode('/', $this->m->Configuration->get('posts.permalink'));
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
                    $this->controller->setRoute('view', 6, array($post->id));
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
                  $this->controller->setRoute('view', 6, array($post->id));
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
            $this->controller->setRoute('view', 3, array($post->id));
          }
        }
      }
    }
  }

  public function getFancyPath($parameters) {
    $id = $parameters[0];
    if ($this->m->Configuration->get('posts.fancyPermalinks') == 'on') {
      $permalink = explode('/', $this->m->Configuration->get('posts.permalink'));
      if (is_array($permalink)) {
        $record = Post::find($id);
        $time = $record->date;
        $replace = array('%name%'  => $record->name,
                         '%id%'    => $id,
                         '%year%'  => tdate('Y', $time),
                         '%month%' => tdate('m', $time),
                         '%day%'   => tdate('d', $time));
        $search = array_keys($replace);
        $replace = array_values($replace);
        $path = array();
        foreach ($permalink as $dir) {
          $path[] = str_replace($search, $replace, $dir);
        }
        return $path;
      }
    }
    return FALSE;
  }

  public function getPath(ActiveRecord $record) {
    $class = get_class($record);
    switch ($class) {
      case 'Post':
        if ($this->m->Configuration->get('posts.fancyPermalinks') == 'on') {
          $permalink = explode('/', $this->m->Configuration->get('posts.permalink'));
          if (is_array($permalink)) {
            $time = $record->date;
            $id = $record->id;
            $id = !isset($id) ? 0 : $record->id;
            $replace = array('%name%'  => $record->name,
                             '%id%'    => $id,
                             '%year%'  => tdate('Y', $time),
                             '%month%' => tdate('m', $time),
                             '%day%'   => tdate('d', $time));
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
    return $this->m->Http->getLink($this->getPath($record));
  }

  public function postController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();

    if ($this->m->Configuration->get('posts.fancyPermalinks') == 'on') {
      $templateData['post'] = Post::find($this->post);
    }
    else {
      $templateData['post'] = Post::find((int) $path[1]);
    }

    if (!$this->m->Http->isCurrent($templateData['post']->getPath())) {
      $this->m->Http->redirectPath($templateData['post']->getPath());
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
      $this->m->Templates->renderTemplate('post.html', $templateData);
    }
  }


  public function newPostController($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();
    $templateData['title'] = tr('New Post');
    $templateData['action'] = $this->m->Http->getLink();

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
    if (isset($_POST['save']) OR isset($_POST['publish'])) {
      $post = Post::create();
      $post->date = time();
      $post->title = $_POST['title'];
      $post->name = Post::createName($_POST['permalink']);
      if ($post->name == '') {
        $post->name = Post::createName($_POST['title']);
      }
      $post->content = $_POST['content'];
      $post->commenting = $_POST['allow_comments'] == 'yes' ? 'yes' : 'no';

      $templateData['values']['title'] = addslashes($_POST['title']);
      $templateData['values']['content'] = addslashes($_POST['content']);
      $templateData['values']['tags'] = addslashes($_POST['tags']);
      $templateData['values']['permalink'] = addslashes($post->name);
      $templateData['values']['allow_comments'] = $post->commenting == 'yes' ? TRUE : FALSE;

      if (!$post->isValid()) {
        $templateData['errors'] = $post->getErrors();
        foreach ($templateData['errors'] as $column => $error) {
          switch ($column) {
            case 'title':
              switch ($error) {
                case 'presence':
                  new LocalWarning(tr('The title of the post cannot be empty.'));
                  break;
                case 'maxLength':
                  new LocalWarning(tr('The title should not be longer than 25 characters.'));
                  break;
                default:
                  break;
              }
              break;
            case 'name':
              switch ($error) {
                case 'presence':
                  new LocalWarning(tr('The permalink of the post cannot be empty.'));
                  break;
                case 'unique':
                  new LocalWarning(tr('The permalink is not unique.'));
                  break;
                default:
                  new LocalWarning(tr('The permalink should be a string consisting of bewteen 1 and 25 alphanumerics characters and dashes.'));
                  break;
              }
              break;
            case 'content':
              new LocalWarning(tr('The content of the post cannot be empty.'));
              break;
            default:
              break;
          }
        }
      }
      else {
        if (isset($_POST['save'])) {
          $post->state = 'draft';
          new LocalNotice(tr('The post has been saved'));
        }
        else {
          $post->state = 'published';
          new LocalNotice(tr('The post has been published'));
        }
        $post->setUser($this->m->Users->getUser());
        $post->save();
        $post->createAndAddTags($_POST['tags']);
        $this->m->Http->refreshPath();
      }
    }
    $this->m->Templates->renderTemplate('backend/edit-post.html', $templateData);
  }
}

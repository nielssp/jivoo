<?php

class PostsController extends AppController {

  protected $helpers = array('Html', 'Pagination', 'Form');

  protected $models = array('User', 'Group', 'Post', 'Comment', 'Tag');

  public function before() {
    parent::before();
    $this->config = $this->config['blog'];
    $this->Format->encoder($this->Post, 'content')->setAllowAll(true);
    $this->Format->encoder($this->Comment, 'content')
      ->allowTag('strong')
      ->allowTag('p')
      ->allowTag('br')
      ->allowTag('em')
      ->allowTag('b')
      ->allowTag('i')
      ->allowTag('u')
      ->allowAttribute('a', 'href')
      ->appendAttributes('a', 'rel="nofollow"');
    $this->Editor->set($this->Comment, 'content', new TextareaEditor('markdown'));
  }

  public function index() {
    $this->posts = $this->Post
      ->where('status = %PostStatus', 'published')
      ->orderByDescending('created');
    $this->posts = $this->Pagination->paginate($this->posts);
    
    $this->view->blocks->relation(
      'alternate', 'application/rss+xml',
      $this->m->Routing->getUrl('feed')
    );

    return $this->render();
  }
  
  public function archive($year = null, $month = null) {
    $this->posts = $this->Post
      ->where('status = %PostStatus', 'published')
      ->orderByDescending('created');
    
    $this->title = tr('Archive');
    
    if (isset($year)) {
      if (isset($month)) {
        $start = strtotime($year . '-' . $month . '-01');
        $end = strtotime($year . '-' . $month . '-01 +1 month');
        $this->title = ucfirst(tdate('F Y', $start));
      }
      else {
        $start = strtotime($year . '-01-01');
        $end = strtotime(($year + 1) . '-01-01');
        $this->title = tdate('Y', $start);
      }
      $this->posts = $this->posts
        ->where('created >= %d', $start)
        ->and('created < %d', $end);
    }
    
    if (isset($this->request->query['q'])) {
      $query = '%' . Condition::escapeLike($this->request->query['q']) . '%';
      $this->posts = $this->posts
        ->where(where('contentText LIKE %s', $query)
          ->or('title LIKE %s', $query));
      $this->title = tr('Search: %1', $this->request->query['q']);
    }
    
    $this->posts = $this->Pagination->paginate($this->posts);
    
    return $this->render();
  }
  
  public function feed() {
    $this->posts = $this->Post
      ->where('status = %PostStatus', 'published')
      ->orderByDescending('created')
      ->limit(30);
    return $this->render('feed/posts.rss');
  }

  public function view($post) {
    $this->reroute();

    $this->post = $this->Post->find($post);

    if (!$this->post or ($this->post->status != 'published'
        and !$this->Auth->hasPermission('Admin.Posts.viewDraft')))
      throw new NotFoundException();

    $this->comments = $this->post->comments
      ->where('status = %CommentStatus', 'approved')
      ->orderBy('created');

    $this->comments = $this->Pagination->paginate($this->comments, 10);

    $this->user = $this->Auth->getUser();

    $this->embed('Comments', 'add', array($post));

    $this->title = $this->post->title;
    return $this->render();
  }

  public function tagIndex() {
    $this->render('not-implemented.html');
  }

  public function viewTag($tag) {
    $this->tag = $this->Tag->where('name = ?', $tag)->first();
    if (!isset($this->tag))
      throw new NotFoundException();

    $this->posts = $this->tag->posts->where('published = true');

    $this->Pagination->setCount($this->posts);

    $this->Pagination->paginate($this->posts);
    $this->title = $this->tag->tag;
    $this->render('posts/index.html');
  }

}

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
  
  public function archive($year = null, $month = null, $day = null) {
    $this->posts = $this->Post
      ->where('status = %PostStatus', 'published')
      ->orderByDescending('created');
    
    $this->title = tr('Archive');
    
    if (isset($year)) {
      if (isset($month)) {
        if (isset($day)) {
          $this->start = strtotime($year . '-' . $month . '-' . $day);
          $end = strtotime($year . '-' . $month . '-' . $day .' +1 day');
          $this->title = ucfirst(fdate($this->start));
          $this->searchType = 'day';
        }
        else {
          $this->start = strtotime($year . '-' . $month . '-01');
          $end = strtotime($year . '-' . $month . '-01 +1 month');
          $this->title = ucfirst(tdate('F Y', $this->start));
          $this->searchType = 'month';
        }
      }
      else {
        $this->start = strtotime($year . '-01-01');
        $end = strtotime(($year + 1) . '-01-01');
        $this->title = tdate('Y', $this->start);
        $this->searchType = 'year';
      }
      $this->posts = $this->posts
        ->where('created >= %d', $this->start)
        ->and('created < %d', $end);
    }
    
    if (isset($this->request->query['q'])) {
      $query = '%' . Condition::escapeLike($this->request->query['q']) . '%';
      $this->posts = $this->posts
        ->where(where('contentText LIKE %s', $query)
          ->or('title LIKE %s', $query));
      $this->query = $this->request->query['q'];
      $this->title = tr('Search: %1', $this->query);
      $this->searchType = 'query';
    }
    
    $this->posts = $this->Pagination->paginate($this->posts, 10);
    
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
        and !$this->Auth->hasPermission('Posts.viewDraft', 'Admin.')))
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

    $this->posts = $this->tag->posts
      ->where('status = %PostStatus', 'published')
      ->orderByDescending('created');

    $this->Pagination->paginate($this->posts);
    $this->title = tr('Tag: %1', $this->tag->tag);
    $this->searchType = 'tag';
    return $this->render('posts/archive.html');
  }

}

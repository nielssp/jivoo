<?php

class PostsController extends AppController {

  protected $helpers = array('Html', 'Pagination', 'Form');

  protected $modules = array('Editors');
  
  protected $models = array('User', 'Group', 'Post', 'Comment', 'Tag');

  public function before() {
    parent::before();
    $this->config = $this->config['blog'];
  }

  public function index() {
    $this->posts = $this->Post
      ->where('status = %PostStatusEnum', 'published')
      ->orderByDescending('createdAt');
    $this->Pagination->setCount($this->posts->count());
    $this->posts = $this->Pagination->paginate($this->posts);
    
    $this->view->resource(
      'alternate', 'application/rss+xml',
      $this->m->Routing->getUrl('feed')
    );

    return $this->render();
  }
  
  public function feed() {
    $this->posts = $this->Post
      ->where('published = true')
      ->orderByDescending('date')
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
      ->where('status = %CommentStatusEnum', 'approved')
      ->orderBy('createdAt');

    $this->Pagination->setLimit(10);

    $this->Pagination->setCount($this->comments->count());
    $this->comments = $this->Pagination->paginate($this->comments);

    $this->user = $this->Auth->getUser();

    $this->Comment->setFieldEditor('content',
      $this->m->Editors->getEditor($this->config['comments']['editor'])
    );

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

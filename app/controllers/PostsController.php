<?php

class PostsController extends AppController {

  protected $helpers = array('Html', 'Pagination', 'Form');

  protected $modules = array('Editors');
  
  protected $models = array('User', 'Group', 'Post', 'Comment', 'Tag');

  public function before() {
    $this->config = $this->config['Posts'];
  }

  public function index() {
    $this->posts = $this->Post
      ->where('published = true')
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

    if (!$this->post OR (!$this->post->published
        AND !$this->Auth->hasPermission('backend.posts.viewDraft'))) {
      return $this->render('404.html');
    }

    $this->comments = $this->post->comments
      ->where('approved = true')
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
    $this->tag = $this->Tag->first(SelectQuery::create()->where('name = ?', $tag));

    $select = SelectQuery::create()->where('status = "published"')
      ->orderByDescending('date');

    $this->Pagination->setCount($this->tag->countPosts(clone $select));

    $this->Pagination->paginate($select);
    $this->posts = $this->tag->getPosts($select);
    $this->title = $this->tag->tag;
    $this->render('posts/index.html');
  }

}

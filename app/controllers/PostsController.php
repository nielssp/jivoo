<?php

class PostsController extends AppController {

  protected $helpers = array('Html', 'Pagination', 'Form');

  protected $modules = array('Editors');
  
  protected $models = array('Posts', 'Comment', 'Tag');

  public function before() {
    $this->config = $this->config['Posts'];
  }

  public function index() {
  //  $select = SelectQuery::create()->where('status = "published"')
  //    ->orderByDescending('date');
  //  $this->Pagination->setCount($this->Post->count(clone $select));

//    $this->Pagination->paginate($select);

    $this->posts = $this->Posts->where('status = "published"')
      ->orderByDescending('createdAt');
    
//    $this->view->resource(
//      'alternate', 'application/rss+xml',
//      $this->m->Routing->getUrl('feed')
//    );

    return $this->render();
  }
  
  public function feed() {
    $select = SelectQuery::create()
      ->where('status = "published"')
      ->orderByDescending('date')
      ->limit(30);
    $this->posts = $this->Post->all($select);
    
    $this->render('feed/posts.rss');
  }

  public function view($post) {
    $this->reroute();

    $this->post = $this->Post->find($post);

    if (!$this->post OR ($this->post->status != 'published'
        AND !$this->Auth->hasPermission('backend.posts.viewDraft'))) {
      return $this->render('404.html');
    }

    $select = SelectQuery::create()->orderBy('date')
      ->where('status = "approved"');

    $this->Pagination->setLimit(10);

    $this->Pagination->setCount($this->post->countComments(clone $select));
    $this->Pagination->paginate($select);

    $this->user = $this->Auth->getUser();

    $this->Comment->setFieldEditor('content',
      $this->m->Editors->getEditor($this->config['comments']['editor'])
    );

    if ($this->Auth->hasPermission('frontend.posts.comments.add')) {
      if ($this->request->isPost() AND $this->request->checkToken()) {
        $this->newComment = $this->Comment->create($this->request->data['comment'],
          array('author', 'email', 'website', 'content')
        );
        if (!empty($this->newComment->website)
          AND preg_match('/^https?:\/\//', $this->newComment->website) == 0) {
          $this->newComment->website = 'http://' . $this->newComment->website;
        }
        if ($this->user) {
          $this->newComment->setUser($this->user);
          $this->newComment->author = $this->user->username;
          $this->newComment->email = $this->user->email;
        }
        $this->newComment->setPost($this->post);
        $this->newComment->ip = $this->request->ip;
        if ($this->config['commentApproval'] == 'on'
          AND !$this->Auth->hasPermission('backend.posts.comments.approve')) {
          $this->newComment->status = 'pending';
        }
        else {
          $this->newComment->status = 'approved';
        }
        if ($this->newComment->isValid()) {
          $this->newComment->save();
          $this->post->comments += 1;
          $this->post->save();
          $this->Pagination->setCount($this->post->comments);

          if (!empty($this->newComment->author)) {
            $this->request->cookies['comment_author'] = $this->newComment
              ->author;
          }
          if (!empty($this->newComment->email)) {
            $this->request->cookies['comment_email'] = $this->newComment->email;
          }
          if (!empty($this->newComment->website)) {
            $this->request->cookies['comment_website'] = $this->newComment
              ->website;
          }

          $this
            ->refresh(array('page' => $this->Pagination->getPages()),
              'comment' . $this->newComment->id
            );
        }
      }
      else {
        $this->newComment = $this->Comment->create();
        if (isset($this->request->cookies['comment_author'])) {
          $this->newComment->author = $this->request->cookies['comment_author'];
        }
        if (isset($this->request->cookies['comment_email'])) {
          $this->newComment->email = $this->request->cookies['comment_email'];
        }
        if (isset($this->request->cookies['comment_website'])) {
          $this->newComment->website = $this->request
            ->cookies['comment_website'];
        }
      }
    }

    $this->comments = $this->post->getComments($select);

    $this->title = $this->post->title;
    $this->render();
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

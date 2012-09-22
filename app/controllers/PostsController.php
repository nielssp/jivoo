<?php

class PostsController extends ApplicationController {

  protected $helpers = array('Html', 'Pagination', 'Form', 'Filtering', 'Backend', 'Json', 'Bulk');

  public function init() {
    $this->Filtering->addSearchColumn('title');
    $this->Filtering->addSearchColumn('content');
    $this->Filtering->addFilterColumn('status');
    $this->Filtering->addFilterColumn('date');
    
    $this->Filtering->addPredefined(tr('Published'), 'status:published');
    $this->Filtering->addPredefined(tr('Draft'), 'status:draft');
    
    $this->Pagination->setLimit(10);
    
    $this->Bulk->addUpdateAction('publish', tr('Publish'), array('status' => 'published'));
    $this->Bulk->addUpdateAction('conceal', tr('Conceal'), array('status' => 'draft'));
    
    $this->Bulk->addDeleteAction('delete', tr('Delete'));
  }
  
  public function index() {
    $select = SelectQuery::create()
      ->where('status = "published"')
      ->orderByDescending('date');
    $this->Pagination->setCount(Post::count());

    $this->Pagination->paginate($select);

    $this->posts = Post::all($select);

    $this->render();
  }

  public function view($post) {
    $this->reroute();

    $this->post = Post::find($post);

    if (!$this->post OR ($this->post->status != 'published'
        AND !$this->auth->hasPermission('backend.posts.viewDraft'))) {
      return $this->render('404.html');
    }

    
    $select = SelectQuery::create()->orderBy('date')->where('status = "approved"');

    $this->Pagination->setLimit(10);
    
    $this->Pagination->setCount($this->post->countComments(clone $select));
    $this->Pagination->paginate($select);
    
    $this->user = $this->auth->getUser();
    
    Comment::setFieldEditor(
      'content',
      $this->m->Editors->getEditor($this->config['comments.editor'])
    );

    if ($this->auth->hasPermission('frontend.posts.comments.add')) {
      if ($this->request->isPost() AND $this->request->checkToken()) {
        $this->newComment = Comment::create(
          $this->request->data['comment'],
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
            AND !$this->auth->hasPermission('backend.posts.comments.approve')) {
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
            $this->request->cookies['comment_author'] = $this->newComment->author;
          }
          if (!empty($this->newComment->email)) {
            $this->request->cookies['comment_email'] = $this->newComment->email;
          }
          if (!empty($this->newComment->website)) {
            $this->request->cookies['comment_website'] = $this->newComment->website;
          }
          
          $this->refresh(
            array('page' => $this->Pagination->getPages()),
            'comment' . $this->newComment->id
          );
        }
      }
      else {
        $this->newComment = Comment::create();
        if (isset($this->request->cookies['comment_author'])) {
          $this->newComment->author = $this->request->cookies['comment_author'];
        }
        if (isset($this->request->cookies['comment_email'])) {
          $this->newComment->email = $this->request->cookies['comment_email'];
        }
        if (isset($this->request->cookies['comment_website'])) {
          $this->newComment->website = $this->request->cookies['comment_website'];
        }
      }
    }
    
    $this->comments = $this->post->getComments($select);
    
    $this->title = $this->post->title;
    $this->render();
  }
  
  public function manage() {
    $this->Backend->requireAuth('backend.posts.manage');
    
    $select = SelectQuery::create()
      ->orderByDescending('date');

    $this->Filtering->filter($select);
    
    if (isset($this->request->query['filter'])) {
      $this->Pagination->setCount(Post::count($select));
    }
    else {
      $this->Pagination->setCount(Post::count());
    }

    if ($this->Bulk->isBulk()) {
      if ($this->Bulk->isDelete()) {
        $query = SelectQuery::create();
      }
      else {
        $query = UpdateQuery::create();
      }
      $this->Filtering->filter($query);
      $this->Bulk->select($query);
      Post::execute($query);
      if (!$this->request->isAjax()) {
        $this->refresh();
      }
    }
    
    $this->Pagination->paginate($select);
    
    $this->posts = Post::all($select);
    $this->title = tr('Manage posts');
    if ($this->request->isAjax()) {
      $html = '';
      foreach ($this->posts as $this->post) {
        $html .= $this->render('posts/post.html', true);
      }
      $this->Json->respond(array('html' => $html));
    }
    else {
      $this->returnToThis();
      $this->render();
    }
  }

  public function add() {
    $this->Backend->requireAuth('backend.posts.add');
    
    $examplePost = Post::create();
    $examplePost->name = '%name%';
    $examplePost->date = time();
    $exampleLink = explode('%name%', $this->m->Routes->getLink($examplePost));
    $examplePost = null;
    $this->nameInPermalink = count($exampleLink) >= 2;
    $this->beforePermalink = $exampleLink[0];
    $this->afterPermalink = $exampleLink[1];
    
    Post::setFieldEditor(
      'content',
      $this->m->Editors->getEditor($this->config['editor'])
    );
    
    if ($this->request->isPost() AND $this->request->checkToken('post')) {
      $this->post = Post::create($this->request->data['post']);
      if (isset($this->request->data['publish'])) {
        $this->post->status = 'published';
      }
      else {
        $this->post->status = 'draft';
      }
      if ($this->post->isValid()) {
        $this->post->setUser($this->auth->getUser());
        $this->post->save();
        if ($this->post->status == 'published') {
          $this->redirect($this->post);
        }
        else {
          new LocalNotice(tr('Post successfully created'));
          $this->refresh();
        }
      }
      else {
        foreach ($this->post->getErrors() as $field => $error) {
          new LocalWarning($this->post->getFieldLabel($field) . ': ' . $error);
        }
      }
    }
    else {
      $this->post = Post::create();
    }
    $this->title = tr('New post');
    $this->render('posts/edit.html');
  }

  public function edit($post = null) {
    $this->Backend->requireAuth('backend.posts.edit');

    $this->post = Post::find($post);
    if (!$this->post) {
      return $this->notFound();
    }
    
    Post::setFieldEditor(
      'content',
      $this->m->Editors->getEditor($this->config['editor'])
    );
    
    if ($this->request->isPost()) {
      $this->post->addData($this->request->data['post']);
      if (isset($this->request->data['publish'])) {
        $this->post->status = 'published';
      }
      else if (!isset($this->request->data['post']['status'])) {
        $this->post->status = 'draft';
      }
      if ($this->post->isValid()) {
        $this->post->save();
        if (!$this->request->isAjax()) {
          $this->goBack();
          if ($this->post->status == 'published') {
            $this->redirect($this->post);
          }
          else {
            new LocalNotice(tr('Post successfully saved'));
            $this->refresh();
          }
        }
      }
      else {
        foreach ($this->post->getErrors() as $field => $error) {
          new LocalWarning($this->post->getFieldLabel($field) . ': ' . $error);
        }
      }
    }
    $examplePost = Post::create();
    $examplePost->name = '%name%';
    $examplePost->date = time();
    $exampleLink = explode('%name%', $this->m->Routes->getLink($examplePost));
    $examplePost = null;
    $this->nameInPermalink = count($exampleLink) >= 2;
    $this->beforePermalink = $exampleLink[0];
    $this->afterPermalink = $exampleLink[1];
    $this->title = tr('Edit post');
    if (!$this->request->isAjax()) {
      $this->render();
    }
    else {
      $this->Json->respond(array(
        'html' => $this->render('posts/post.html', true)
      ));
    }
  }

  public function delete($post) {
    $this->Backend->requireAuth('backend.posts.delete');
    
    $this->render('not-implemented.html');
  }

  public function tagIndex() {
    $this->render('not-implemented.html');
  }
  
  public function viewTag($tag) {
    $this->tag = Tag::first(SelectQuery::create()
      ->where('name = ?', $tag)
    );

    /** @todo This includes unpublished posts */
    $this->Pagination->setCount($this->tag->countPosts());

    $select = SelectQuery::create()
      ->where('status = "published"')
      ->orderByDescending('date');

    $this->Pagination->paginate($select);

    $this->posts = $this->tag->getPosts($select);

    $this->title = $this->tag->tag;

    $this->render('posts/index.html');
  }
  
  public function manageTags() {
    $this->Backend->requireAuth('backend.tags.manage');
    $this->title = tr('Tags');
    $this->tags = Tag::all();
    $this->render();
  }
  
}

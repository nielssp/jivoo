<!DOCTYPE html>
<html>
<head>
<title>My Web App</title>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap-theme.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>

</head>
<body>

<div class="container">
  <div class="header">
    <ul class="nav nav-pills pull-right">
      <li><?php echo $Html->link('Home'); ?></li>
      <li><?php echo $Html->link('Add post', 'Posts::add'); ?></li>
    </ul>
    <h3 class="text-muted">My Web App</h3>
  </div>

  <div class="content">
    <?php echo $this->block('content'); ?>
  </div>

</div>

</body>
</html>

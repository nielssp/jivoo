<!DOCTYPE html>
<html>
  <head>
    <title>Ajax Test</title>

    <meta http-equiv="content-type" content="text/html;charset=utf-8" />

    <script type="text/javascript" src="/GOTUN/PeanutCMS/extensions/Jquery/js/jquery-1.7.1.min.js"></script>

    <script type="text/javascript">
      $(document).ready(function() {
        $.ajax({
          url: '/GOTUN/PeanutCMS/index.php/2012/06/test5',
          data: {
            test: 23,
            foo: 'bar'
          },
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            var items = [];

            $.each(data, function(key, val) {
              items.push('<li id="' + key + '">' + key + '  = ' + val + '</li>');
            });

            $('<ul/>', {
              'class': 'my-new-list',
              html: items.join('')
            }).prependTo('div');
          }
        });
      });
    </script>

  </head>
  <body>

    <div>test</div>

  </body>
</html>

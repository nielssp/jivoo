var client = function() {
  var token = document.forms[0].elements['access_token'].value;

  document.forms[0].elements['next'].disabled = true;
  
  var progress = document.getElementById('install-progress').children[0];
  var label = document.getElementById('install-progress').children[1];
  var updateStatus = function(message, error) {
    label.innerText = message;
    var status = document.createElement('div');
    if (error) {
      document.getElementById('install-progress').className = 'progress error';
      status.className = 'error';
    }
    if (status.textContent !== undefined)
      status.textContent = message;
    else
      status.innerText = message;
    document.getElementById('install-status').style.display = 'block';
    document.getElementById('install-status').appendChild(status);
  };
  var updateProgress = function(pct) {
    document.getElementById('install-progress').style.display = 'block';
    if (pct >= 100)
      document.getElementById('install-progress').className = 'progress success';
    else
      document.getElementById('install-progress').className = 'progress primary active';
    progress.style.width = pct + '%';
    progress.innerText = pct + '%';
  };

  var post = function(url, success) {
    var request = new XMLHttpRequest();
    request.open('POST', url, true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    request.onreadystatechange = function() {
      if (this.readyState === 3 || this.readyState === 4) {
        if (this.status >= 200 && this.status < 400) {
          if (this.responseText)
            success(this.responseText, this.readyState, this.status);
        }
      }
    };
    
    request.send('access_token=' + token);
  }
  
  var done = false;
  var repeat = function() {
    var received = 0;
    post(location.href, function(text, state, status) {
      var events = text.split(/[\n\r]/);
      for (var i = received ; i < events.length; i++) {
        var matches = events[i].match(/^([a-zA-Z]+): *(.*)$/);
        if (matches !== null) {
          received++;
          var type = matches[1];
          var data = matches[2];
          switch (type) {
            case 'done':
              document.forms[0].elements['next'].disabled = false;
              updateProgress(100);
              done = true;
              return;
            case 'error':
              done = true;
              updateStatus(data, true);
              return;
            case 'status':
              updateStatus(data, false);
              break;
            case 'progress':
              updateProgress(data);
              break;
          }
        }
      }
      if (!done && state === 4) {
        if (received == 0)
          setTimeout(repeat, 2000);
        else
          repeat();
      }
    });
  };
  repeat();
}

if (document.addEventListener)
  document.addEventListener('DOMContentLoaded', client);
else
  document.attachEvent('onreadystatechange', function() {
    if (document.readyState != 'loading')
      client();
  });
var client = function() {
  var token = document.forms['install'].elements['access_token'].value;

  document.forms['install'].elements['next'].disabled = true;
  
  var request = new XMLHttpRequest();
  request.open('POST', location.href, true);
  request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
  request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  
  var received = {};
  
  if (request.onprogress === undefined) {
    
  }
  
  request.onprogress = function(e) {
    var events = e.target.responseText.split(/[\n\r]/);
    for (i = 0; i < events.length; i++) {
      var matches = events[i].match(/^id: *([0-9]+) *([a-zA-Z]+): *(.*)$/);
      if (matches !== null) {
        var id = matches[1];
        var type = matches[2];
        var content = matches[3];
        if (!received.hasOwnProperty(id)) {
          console.log('Received ' + type + ': ' + content);
          if (type === 'status') {
            var status = document.createElement('div');
            if (status.textContent !== undefined)
              status.textContent = content;
            else
              status.innerText = content;
            document.getElementById('install-status').appendChild(status)
          }
          received[id] = {
            type: type,
            content: content
          };
        }
      }
    }
  };

  console.log('start');
  
  request.onreadystatechange = function() {
    console.log(this.readyState);
    if (this.readyState === 4) {
      if (this.status >= 200 && this.status < 400) {
        document.forms['install'].elements['next'].disabled = false;
      }
    }
  };
  
  request.send('access_token=' + token);
}

if (document.addEventListener)
  document.addEventListener('DOMContentLoaded', client);
else
  document.attachEvent('onreadystatechange', function() {
    if (document.readyState != 'loading')
      client();
  });
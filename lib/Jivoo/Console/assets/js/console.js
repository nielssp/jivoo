window.onload = function() {
  var status = document.createElement('div');
  status.id = 'jivoo-console-bar';
  status.innerHTML = 'Jivoo Console | Log (' + jivooLog.length + ')';
  document.body.appendChild(status);
  status.onclick = function () {
    var log = document.getElementById('jivoo-console-log');
    if (log == undefined) {
      log = document.createElement('div');
      log.id = 'jivoo-console-log';
      document.body.appendChild(log);
      jivooLog.forEach(function(entry) {
        var logEntry = document.createElement('div');
        logEntry.className = 'jivoo-console-log-entry';
        logEntry.innerHTML = entry.message;
        if (entry.file) {
          logEntry.innerHTML += ' in <em>' + entry.file + '</em> on line <strong>' + entry.line + '</strong>';
        }
        switch (entry.type) {
        case 1: // QUERY
          logEntry.style.color = '#999';
          break;
        case 2: // DEBUG
          logEntry.style.color = '#99f';
          break;
        case 2: // NOTICE
          logEntry.style.color = '#f00';
          break;
        case 4: // WARNING
          logEntry.style.color = '#f90';
          break;
        case 8: // ERROR
          logEntry.style.color = '#f00';
          break;
        }
        log.appendChild(logEntry);
      });
    }
    else {
      log.hidden = !log.hidden;
    }
  };
}
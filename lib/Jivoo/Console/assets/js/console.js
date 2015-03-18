window.onload = function() {
  var status = document.createElement('div');
  status.id = 'jivoo-console-bar';
  status.innerHTML = 'Jivoo | Log (' + jivooLog.length + ')';
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
        log.appendChild(logEntry);
      });
    }
    else {
      log.hidden = !log.hidden;
    }
  };
}

window.onload = function() {
  var status = document.createElement('div');
  status.id = 'jivoo-console-bar';
  status.innerHTML = 'Jivoo | Log (' + jivooLog.length + ')';
  document.body.appendChild(status);
}

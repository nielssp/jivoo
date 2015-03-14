window.onload = function() {
  var status = document.createElement('div');
  status.innerHTML = 'Development mode | Log (0) | Request data';
  status.style.position = 'fixed';
  status.style.left = '10px';
  status.style.bottom = '10px';
  status.style.backgroundColor = '#eee';
  status.style.border = '1px solid #ccc';
  status.style.padding = '5px 10px';
  status.style.color = '#c00';
  console.log(status.style);
  document.body.appendChild(status);
}

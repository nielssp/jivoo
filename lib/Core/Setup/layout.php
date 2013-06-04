<!DOCTYPE html>
<html>
<head>
<title><?php echo $title; ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
* {
  padding: 0;
  margin: 0;
}

body {
  background: #eee url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAIACAYAAABD1gYFAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3QYECykKypBesgAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAABqUlEQVRIx41WSZLDIAzs1v+fl3lPzwGsDZH4kipsI6TeCD6fPxkA/PghAFM8I2GUYCBgEmGAYCRggi9FGCh/BhghGHw5nkHsvcJzBoRdHqnos23VE/a5a8mhvJeKokhV2pSAMXVaGheU33aE8OxdI9RP1jY+LyDARB5Vrlil/uowCfvFDDIpuZ782U/2EwEOInXOC/rhzsf6OH1XOgA4Q3fUc+hiSjpCgUGtnPcOgmPhQ6uKitYSVk0M+Nq4tk6zqIsrxBj/K8kjdIMEAqGk+xONcE/6pHphI+nAVo03RcA1ngGrLuuuUIWkiKt5MIbpUh7Lh/d91EnA3eLNg1O06Em9FiM1bpY6g/2ksEKKXJgz08HbGYchwtAQartj+RsfPa/WaSUeFGHEQaddOTxjWJqFtJVTZktMq+m5BdktbRNgVXU8/XuHvb5Isq3OS5F7wy+DU4Ls4IMXn0eq1MiQpjRrpHCwxtlVtouy7rPgXjR+VXFdyjGtbNHd0y5r3i6ldlASFzFfvc1HL6wx/YVIgedREAojhgh643gOeVWvSlfYwlTNl2+vE+AftSboxck2AkYAAAAASUVORK5CYII=) top repeat-x fixed;
  font-family: "Segoe UI", Tahoma, Arial, sans-serif;
  font-size: 63%;
}

h1 {
  font-size: 40px;
  color: #252830;
  font-weight: normal;
  line-height: 150px;
}

h2 {
  font-size: 20px;
  color: #252830;
  font-weight: normal;
  margin: 5px 0 10px;
}

strong,em {
  color: #252830;
}

#header {
  position: fixed;
  top: 0px;
  left: 0px;
  width: 100%;
  height: 50px;
}


#header #bar {
  background: #000 url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAgCAYAAADT5RIaAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3QYECyktb5rr2QAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAANElEQVQI13WMQQoAIAzDwthH/f8XdPVWRdyltISGkakoIABiupUDx5Lu2d4eQXXgqCR9wQYhXhZ9vEbfAAAAAABJRU5ErkJggg==) repeat-x;
  height: 32px;
  color: #fff;
  border-top: 1px solid #666;
  border-bottom: 1px solid #222;
  box-shadow: 1px 1px 16px #555;
}

#header div.right {
  line-height: 32px;
  font-size: 1.6em;
  text-align: right;
  float: right;
  padding-right: 40px;
  margin-right: 8px;
  text-shadow: 1px 1px 2px #000;
}

#header div.right a:link,#header div.right a:visited,#header div.right a:active
  {
  text-decoration: none;
  color: #fff;
}

#header div.right a:hover {
  text-decoration: underline;
}

#content {
  margin-top: 64px;
  margin-bottom: 64px;
  text-align: center;
}

#content a:link,#content a:visited,#content a:active {
  text-decoration: none;
  color: inherit;
}

#content a:hover {
  text-decoration: underline;
  color: inherit;
}

.footer {
  position: fixed;
  bottom: 0px;
  padding: 4px 16px;
  background-color: #ddd;
  color: #666;
  font-size: 1.2em;
}

.footer a:link,.footer a:visited,.footer a:active {
  color: inherit;
  font-weight: bold;
  text-decoration: none;
}

.footer a:hover {
  color: inherit;
  font-weight: bold;
  text-decoration: underline;
}

#poweredby {
  left: 0px;
  border-top-right-radius: 4px;
}

#links {
  right: 0px;
  border-top-left-radius: 4px;
}

.section {
  margin: 16px 0;
  text-align: left;
}

.header_section h1 {
  text-align: center;
  font-size: 4.5em;
  line-height: normal;
  margin: 128px 0 32px;
  color: #242930;
}

.header_section h1 a:link,.header_section h1 a:visited,.header_section h1 a:active
  {
  color: inherit;
  text-decoration: none;
  font-weight: normal !important;
}

.header_section h1 a:hover {
  color: inherit;
  text-decoration: underline;
  font-weight: normal !important;
}

.container {
  margin: 0 auto;
  width: 768px;
}

.container .error {
  color: #a22121;
}

.narrow_container {
  width: 368px;
}

.center {
  text-align: center;
}

p {
  margin-bottom: 16px;
  text-align: left;
  font-size: 1.3em;
}

p.right {
  text-align: right;
}

input {
  font-family: "Segoe UI", Tahoma, Arial, sans-serif;
}

input.text,textarea {
  font-size: 12px;
  width: 750px;
  padding: 4px 8px;
  border: 1px solid #999;
  border-radius: 4px;
  color: #333;
  font-family: "Segoe UI", Tahoma, Arial, sans-serif;
  background-color: #fff;
}

.narrow_container input.text,.narrow_container textarea {
  width: 350px;
}

input.bigtext {
  font-size: 20px;
}

input.text:focus,textarea:focus {
  outline: none;
  box-shadow: 0 0 16px 0px #444;
}


#sad {
  font-size: 100px;
  color: #540303;
  float: left;
  margin-right: 30px;
}

h1 {
  font-size: 40px;
  color: #540303;
  font-weight: normal;
  line-height: 150px;
}

h2 {
  font-size: 20px;
  color: #540303;
  font-weight: normal;
  margin: 5px 0 10px;
}

strong,em {
  color: #540303;
}

code {
  font-family: Consolas, monospace;
}

table.trace {
  width: 100%;
  border-collapse: collapse;
  border: solid 1px #540303;
  font-size: 1.2em;
}

table thead tr {
  background-color: #705151;
}

table thead th {
  color: #fff;
  padding: 2px 4px 2px 4px;
  font-weight: normal;
}

table.trace tr.odd {
  background-color: #fff;
}

table.trace tr.even {
  background-color: #EEE;
}

table.trace td {
  padding: 2px 4px 2px 4px;
}

</style>
</head>
<body>

<div id="header">
<div id="bar">
<div class="right"><?php echo $app; ?></div>
</div>
</div>

<div id="content">
<div class="section">
<div class="container">
<div id="sad">
:-(
</div>
<h1><?php echo $title; ?></h1>

<div class="clearl"></div>

<?php
if (isset($exception)) {
  include $this->p('exception.php');
}
else {
  echo $body;
}
?>

</div>
</div>
</div>

<div class="footer" id="poweredby">
Powered by <a href="#"><?php echo $app; ?> 
<?php echo $version; ?></a>
</div>

<div class="footer" id="links">
<a href="http://apakoh.dk">Help</a>
</div>

</body>
</html>
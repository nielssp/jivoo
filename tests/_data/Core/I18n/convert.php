<?php
// script that converts the 'da.mo' file to big endian

$f = file_get_contents('da.mo');

if ($f === false) {
  exit('missing file');
}

$magic = bin2hex(substr($f, 0, 4));
if ($magic === '950412de') { // Big endian
  $header = 'Nrev/NN/NO/NT/NS/NH';
  $format = 'N';
}
else if ($magic === 'de120495') { // Little endian
  $header = 'Vrev/VN/VO/VT/VS/VH';
  $format = 'V';
}
else {
  exit('not valid');
}
$o = 4;

$data = unpack($header, substr($f, $o, 24));
$num = $data['N'];
$oOffset = $data['O'];
$tOffset = $data['T'];

if ($num == 0)
  exit('empty');

$format = $format . ($num * 2);

$o = $oOffset;
$oTable = unpack($format, substr($f, $o, 8 * $num));
$o = $tOffset;
$tTable = unpack($format, substr($f, $o, 8 * $num));

$new = "\x95\x04\x12\xde";

$new .= pack('NNNNNN', $data['rev'], $data['N'], $data['O'], $data['T'], $data['S'], $data['H']);
$o = 28;
while ($o < $oOffset) {
  $new .= "\0";
  $o++;
}
foreach ($oTable as $offset) {
  $new .= pack('N', $offset);
  $o += 4;
}
while ($o < $tOffset) {
  $new .= "\0";
  $o++;
}
foreach ($tTable as $offset) {
  $new .= pack('N', $offset);
  $o += 4;
}
$new .= substr($f, $o);
  

file_put_contents('da.be.mo', $new);

<?php
include('../app/essentials.php');

echo '<pre>';

$format = new TextFormat();

$editorContent = <<<END
Hej verden
hvordan går det?

Halløj igen! Tjek min side http://apakoh.dk
END;

$dbContent = $format->toHtml($editorContent);
echo 'In db:' . PHP_EOL;
echo h($dbContent);
echo PHP_EOL . PHP_EOL;
echo 'In editor:' . PHP_EOL;
echo h($format->fromHtml($dbContent));


echo '</pre>';

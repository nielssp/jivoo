<p><?php echo tr('Welcome to the Jivoo web application framework version %1.', '<strong>' . \Jivoo\VERSION . '</strong>')?></p>

<p><?php echo tr(
  'No valid application configuration was found in the specified application directory:'
); ?></p>

<pre><?php echo h($appDir); ?></pre>

<p class="info"><?php echo tr('Do you want Jivoo to generate the files for a new application?')?></p>
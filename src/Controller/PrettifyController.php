<?php

/**
 * @file
 * Contains \Drupal\prettify\Controller\PrettifyController.
 */

namespace Drupal\prettify\Controller;

/**
 * Returns responses for Prettify routes.
 */
class PrettifyController {

  public function demo($css) {
    $config = config('prettify.settings');
    $base_path = base_path();
    $library_path = $base_path . prettify_library_get_path();
    $module_path = $base_path . drupal_get_path('module', 'prettify');
    if ($css == 'default') {
      $css_path = $library_path . '/prettify.css';
    }
    else {
      $css_path = $module_path . '/styles/'. $css .'.css';
    }
    $linenums = $config->get('behaviour_linenums');
    $class = 'prettyprint lang-html';
    if ($linenums) {
      $class .= ' linenums';
    }

    $output = <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Theme $css</title>
    <script type="text/javascript" src="$library_path/prettify.js"></script>
    <script type="text/javascript" src="$library_path/lang-css.js"></script>
    <link type="text/css" rel="stylesheet" media="all" href="$css_path" />
    <style type="text/css">
      body { margin: 0; padding: 0 }
      pre { margin: 0 }
    </style>
    <script type="text/javascript">
      // Call out to the parent so that it can resize the iframe once this
      // document's body is loaded.
      function adjustHeightInParent() {
        if (parent !== window) {
          try {
            var div = document.body.getElementsByTagName('div')[0];
            parent.adjustChildIframeSize('$css', div.offsetWidth, div.offsetHeight);
          } catch (ex) {
            // Can happen when this page is opened in its own tab.
          }
        }
      }
    </script>
  </head>
  <body onload="prettyPrint(); adjustHeightInParent()">
    <div style="width: 40em; display: inline-block">
<pre class="$class">
&lt;script type="text/javascript"&gt;
// Say hello world until the user starts questioning
// the meaningfulness of their existence.
function helloWorld(world) {
  for (var i = 42; --i &gt;= 0;) {
    alert('Hello ' + String(world));
  }
}
&lt;/script&gt;
&lt;style&gt;
p { color: pink }
b { color: blue }
u { color: "umber" }
&lt;/style&gt;
</pre>
    </div>
  </body>
</html>
EOD;

    print $output;
  }

}

function _prettify_get_options_styles() {
  return [
    'default' => $this->t('Default'),
    'desert' => $this->t('Desert vim theme (by <a href="@url">anatoly techtonik</a>)', ['@url' => 'https://code.google.com/u/techtonik@gmail.com/']),
    'sunburst' => $this->t('Sunburst vim theme (by David Leibovic)'),
    'sons-of-obsidian' => $this->t('Sons of Obsidian theme (by <a href="@url">Alex Ford</a>)', ['@url' => 'http://codetunnel.com/blog/post/71/google-code-prettify-obsidian-theme']),
    'doxy' => $this->t('Doxy (by by Robert Sperberg)'),
  ];
}

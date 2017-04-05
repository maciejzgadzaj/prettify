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

  /**
   * Provide the administration overview page.
   *
   * @return array
   *   A renderable array of the administration overview page.
   */
  public function overview() {
    $script = <<<EOD
      // Called by the demo.html frames loaded per theme to
      // size the iframes properly and to allow them to tile
      // the page nicely.
      function adjustChildIframeSize(themeName, width, height) {
        var container = document.getElementById(themeName).parentNode;
        container.style.width = (+width + 16) + 'px';
        container.style.display = 'inline-block';
        var iframe = container.getElementsByTagName('iframe')[0];
        iframe.style.height = (+height + 16) + 'px';
      }
EOD;

    drupal_add_js($script, 'inline');

    $output = '<p>'. t('Configure the theme in <a href="@url">Code Prettify settings page</a>. Print preview this page to see how the themes work on the printed page.', array('@url' => url('admin/settings/prettify'))) .'</p>';

    $styles = _prettify_get_options_styles();
    foreach ($styles as $css => $name) {
      $url = url("prettify/gallery/$css");
      $output .= '<div>';
      $output .= '<h3 style="margin-bottom: 0.5em">'. $name .'</h3>';
      $output .= '<iframe id="'. $css .'" src="'. $url .'" style="width: 100%; border-style: none; margin: 0; padding: 0"></iframe>';
      $output .= '</div>';
    }

    return $output;
  }

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
  return array(
    'default' => t('Default'),
    'desert' => t('Desert vim theme (by <a href="@url">techto&hellip;@</a>)', array('@url' => 'http://code.google.com/u/@VhJeSlJYBhVMWgF7')),
    'sunburst' => t('Sunburst vim theme (by David Leibovic)', array('@url' => 'http://stackoverflow.com')),
    'googlecode' => t('Google Code (<a href="@url">code.google.com</a>)', array('@url' => 'http://code.google.com')),
    'stackoverflow' => t('Stack Overflow (<a href="@url">stackoverflow.com</a>)', array('@url' => 'http://stackoverflow.com')),
    'naspinski' => t('Naspinski (<a href="@url">naspinski.net</a>)', array('@url' => 'http://naspinski.net')),
    'drupalorg' => t('Drupal.org (<a href="@url">drupal.org</a>)', array('@url' => 'http://drupal.org')),
    'cobalt' => t('Cobalt Textmate adaptation (by <a href="@url">cartuchogl</a>)', array('@url' => 'http://groups.google.com/group/js-code-prettifier/browse_thread/thread/2a504720992aec6d/73b5bc2300c15d4f')),
  );
}

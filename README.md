# Google Code Prettify

## Summary

Simple and lightweight syntax highlighting of source code snippets using [Google JavaScript code prettifier library](https://github.com/google/code-prettify) for Drupal.

Google JavaScript code prettifier library supports all C-like (Java, PHP, C#, etc), Bash-like, and XML-like languages without need to specify the language and has customizable styles via CSS. Widely used with good cross-browser support.


## Requirements

 - Google [JavaScript code prettifier](https://github.com/google/code-prettify) library


## Installation

 - Download the latest Google JavaScript code prettifier library from https://github.com/google/code-prettify

   Extract the content into the `libraries/prettify` directory. The main `prettify.js` file should be available at `libraries/prettify/src/prettify.js`.

 - Enable module as usual.


## Usage and configuration

 - Out of the box, code prettify comes configured to automatically perform syntax highlighting of source code snippets in `<pre>...</pre>` or `<code>...</code>` tags of your Drupal site.
  
   Automatic syntax highlighting mode is pretty simple, but powerful at the same time. Several settings can be configured at _Administration > Configuration > User interface > Code prettify_

 - In addition, code prettify module also provides a filter to allow users can post code verbatim (without having to worry about manually escaping `<` and `>` characters).
  
   Prettify filter can be enabled and configured at _Administration > Configuration > Content authoring > Text formats and editors_


## Tips & tricks

 - If you use a WYSIWYG editor is recommended use the automatic syntax highlighting mode.

 - You don't need to specify the language of source code snippets since prettify will guess, but you can specify a language by [specifying the language extension with the class](https://github.com/google/code-prettify/blob/master/README.md#how-do-i-specify-the-language-of-my-code).

 - This module includes several themes to customize the colors and styles of source code snippets. See the [theme gallery](https://rawgit.com/google/code-prettify/master/styles/index.html) for examples. You can create your own custom CSS styles, too.


## Developers

Code prettify module provides a simple API for use by other modules and themes.

### The server-side API

* prettify_add_library()

  Adds the prettify javascript and stylesheets to the current page. You
  should use this when you wish to use the client-side API to be pretty printed.

 - `theme('prettify', ['text' => $code])`

   Returns the HTML of source code snippets will automatically be pretty printed.

### The client-side API

 - `Drupal.prettify.prettifyBlock(element)`

   Use this method to syntax highlighting of the source code snippets. For example:

   ```
   $('pre code').each(function(i, e) {
     Drupal.prettify.prettifyBlock(e)}
   );
   ```


## Credits

Author and maintainer:
 - Sergio Martín Morillas (smartinm) - http://drupal.org/user/191570

This module includes several CSS styles publicly available which are used as themes for code prettify. Go to configure administration page for more info.

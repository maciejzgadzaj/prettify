(function ($, Drupal, drupalSettings) {

Drupal.prettify = Drupal.prettify || {};

/**
 * Attach prettify loader behavior.
 */
Drupal.behaviors.prettify = {
  attach: function (context, settings) {

    console.log(drupalSettings);
    if (drupalSettings.prettify.match) {
      context = drupalSettings.prettify.match;
    }

    if (drupalSettings.prettify.markup['code']) {
      console.log(context);
      // Selector for <code>...</code>
      $("code:not(.prettyprint)", context).not($("pre > code", context)).each(function () {
        Drupal.prettify.prettifyBlock($(this));
      });
    }

    if (drupalSettings.prettify.markup.pre) {
      // Selector for <pre>...</pre>
      $("pre:not(.prettyprint)", context).each(function () {
        Drupal.prettify.prettifyBlock($(this));
      });
    }
    else if (drupalSettings.prettify.markup.precode) {
      // Selector for <pre><code>...</code></pre>
      $("pre:not(.prettyprint) > code", context).parent().each(function () {
        Drupal.prettify.prettifyBlock($(this));
      });
    }

    // Process custom markup selectors
    for (var i = 0; i < drupalSettings.prettify.custom.length; i++) {
      var selector = drupalSettings.prettify.custom[i];
      if (selector) {
        $(selector, context).each(function () {
          if (!$(this).hasClass('prettyprint')) {
            codeBlock = $(this).parent().is('pre') ? $(this).parent() : $(this);
            Drupal.prettify.prettifyBlock(codeBlock);
          }
        });
      }
    }

    if ($(".prettyprint").length > 0) {
      prettyPrint();
    }
  }
};

Drupal.prettify.prettifyBlock = function(codeBlock) {
  if (!codeBlock.hasClass(drupalSettings.prettify.nocode)) {
    codeBlock.addClass("prettyprint");
    if (drupalSettings.prettify.linenums && codeBlock.is('pre')) {
      codeBlock.addClass("linenums");
    }
  }
}

})(jQuery, Drupal, drupalSettings);

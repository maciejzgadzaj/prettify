<?php
/**
 * @file
 * Contains \Drupal\prettify\Form\PrettifyAdminForm.
 */

namespace Drupal\prettify\Form;

use Drupal\Core\Form\ConfigFormBase;

/**
 * Defines a form to configure maintenance settings for this site.
 */
class PrettifyAdminForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'prettify_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    // Get our config settings.
    $config = \Drupal::config('prettify.settings');
    $library_path = prettify_library_get_path();
    if (!file_exists("$library_path/prettify.js")) {
      drupal_set_message(t('Could not find Google Code Prettify JavaScript library. Check the <a href="@status">status report</a> for more information.', array('@status' => url('admin/reports/status'))), 'error');
    }
    //@todo add the js using #attach ?
    //drupal_add_js(drupal_get_path('module', 'prettify') . '/prettify.admin.js');

    $form['auto'] = array(
      '#type' => 'fieldset',
      '#title' => t('Automatic syntax highlighting settings'),
    );

    /*
    $form['auto']['auto'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable automatic syntax highlighting'),
      '#description' => t('Code prettify automatically applies syntax highlighting of source code snippets in your Drupal site. If you only want use the code pretiffy filter or insert manually markup for code snippets, you can uncheck this checkbox to disable automatic syntax highlighting.'),
      '#default_value' => $config->get('auto'),
    );
    */

    $form['auto']['auto_markup'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Markup for code blocks'),
      //'#description' => t('Choose the HTML tags which will be used to automatically apply syntax highlighting of source code snippets in your Drupal site.'),
      '#description' => t("Choose which DOM elements containing source code to highlight. The language of the code will be auto-detected for Prettify."),
      '#default_value' => $config->get('auto_markup'),
      '#options' => array(
        PRETTIFY_MARKUP_CODE    => '<code>'. check_plain('<code>...</code>') .'</code>',
        PRETTIFY_MARKUP_PRE     => '<code>'. check_plain('<pre>...</pre>') .'</code>',
        PRETTIFY_MARKUP_PRECODE => '<code>'. check_plain('<pre><code>...</code></pre>') .'</code>',
      ),
    );

    $description = t("Constrains syntax highlighting to within the bounds of the specified element - can be a DOM element or a jQuery selector, like 'div.node-type-story, .block .content'. Empty, for  document element.");
    $form['auto']['auto_element_class'] = array(
      '#type' => 'textfield',
      '#title' => t('Containment element'),
      '#default_value' => $config->get('auto_element_class'),
      '#description' => $description,
    );

    $form['auto']['auto_disabled_class'] = array(
      '#type' => 'textfield',
      '#title' => t('Class to disable highlighting'),
      '#default_value' => $config->get('auto_disabled_class'),
      '#description' => t('Don\'t apply automatic syntax highlighting if HTML tags has this classes. Empty, for all tags.'),
      '#description' => t('You can use this HTML class to identify a block that is not code, like &lt;pre class="nocode"&gt;...&lt;/pre&gt;.'),
      '#description' => t('Use this class to disable highlighting of a fragment, like &lt;pre class="nocode"&gt;...&lt;/pre&gt;.')
    );

    $args = array('%selector1' => '.node .field-field-source-code pre', '%selector2' => 'pre');
    $description = t("Enter one jQuery selector per line. Example selectors are %selector1 or %selector2.", $args);
    $description = t('If you use different markup for code blocks you can use jQuery selectors to select elements containing the code to highlight. Enter one jQuery selector per line, example selectors are %selector1 or %selector2.', $args);
    $form['auto']['auto_custom_markup'] = array(
      '#type' => 'textarea',
      '#title' => t('Custom markup for code blocks'),
      //'#title' => t('jQuery selectors'),
      '#default_value' => $config->get('auto_custom_markup'),
      '#description' => $description,
      '#rows' => 3,
    );

    // -- Default behaviour settings

    $form['global'] = array(
      '#type' => 'fieldset',
      '#title' => t('Global settings'),
      //'#collapsible' => TRUE,
    );

    $styles = _prettify_get_options_styles();
    $styles['custom'] = t('Define CSS');
    $form['global']['css'] = array(
      '#type' => 'radios',
      '#title' => t('Styles'),
      '#default_value' => $config->get('css'),
      '#options' => $styles,
      '#description' => t('Defines the stylesheets to be used. View <a href="@url">gallery of themes</a>.', array('@url' => url('prettify/gallery'))),
    );

    $form['global']['css_path'] = array(
      '#type' => 'textfield',
      '#title' => t('Custom CSS path'),
      '#default_value' => $config->get('css_path'),
      '#description' => t('If "Define CSS" was selected above, enter external URL or path to a CSS file. Available tokens: <code>%b</code> (base path, eg: <code>/</code>), <code>%t</code> (path to theme, eg: <code>themes/garland</code>)') . '<br />' . t('Example:') . ' css/prettify.css,/themes/garland/prettify.css,%b%t/prettify.css,http://example.com/external.css',
    );

    $form['global']['behaviour_linenums'] = array(
      '#type' => 'select',
      '#title' => t('Show line numbers'),
      '#description' => t('Turn on line numbering by default.'),
      '#default_value' => $config->get('behaviour_linenums'),
      '#options' => array(t('No'), t('Yes')),
    );

    $behaviour_extensions = $config->get('behaviour_extensions');
    $form['global']['behaviour_extensions'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Optionally language extensions'),
      '#description' => t("Because of commenting conventions, Prettify doesn't work on Smalltalk, Lisp-like, or CAML-like languages without an explicit lang class."),
      '#default_value' => $behaviour_extensions ? $behaviour_extensions : array(),
      '#options' => _prettify_get_options_lang_extensions(),
    );

    // --

    $form['page_load_settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Page specific activation settings'),
      '#collapsible' => TRUE,
    );

    $options = array(
      t('Load on every page except the listed pages.'),
      t('Load on only the listed pages.'),
    );

    $description = t("Enter one page per line as Drupal paths. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", array('%blog' => 'blog', '%blog-wildcard' => 'blog/*', '%front' => '<front>'));

    if (user_access('use PHP for settings')) {
      $options[] = t('Load if the following PHP code returns <code>TRUE</code> (PHP-mode, experts only).');
      $description .= ' '. t('If the PHP-mode is chosen, enter PHP code between %php. Note that executing incorrect PHP-code can break your Drupal site.', array('%php' => '<?php ?>'));
    }

    $form['page_load_settings']['activation_mode'] = array(
      '#type' => 'radios',
      '#title' => t('Load Prettify on specific pages'),
      '#options' => $options,
      '#default_value' => $config->get('activation_mode'),
    );
    $form['page_load_settings']['activation_pages'] = array(
      '#type' => 'textarea',
      '#title' => t('Pages'),
      '#default_value' => $config->get('activation_pages'),
      '#description' => $description,
    );

    // --

    $form['advanced_settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Advanced settings'),
      '#collapsible' => TRUE,
    );

    /*
    $form['advanced_settings']['custom'] = array(
      '#type' => 'fieldset',
      '#title' => t('Custom automatic syntax highlighting'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    */

    $form['advanced_settings']['add_js_scope'] = array(
      '#type' => 'select',
      '#title' => t('Prettify JavaScript scope'),
      '#options' => array('header' => t('Header'), 'footer' => t('Footer')),
      '#default_value' => $config->get('add_js_scope'),
      '#description' => t('The location in which you want to place the Prettify script (default: Header).'),
    );

    $form['advanced_settings']['add_js_preprocess'] = array(
      '#type' => 'checkbox',
      '#title' => t('Prettify JavaScript preprocess'),
      '#default_value' => $config->get('add_js_preprocess'),
      '#description' => t('If is checked, the Prettify JS file be aggregated if this feature has been turned on under the performance section (default: checked).'),
    );

    $form['advanced_settings']['add_js_defer'] = array(
      '#type' => 'checkbox',
      '#title' => t('Prettify JavaScript defer'),
      '#default_value' => $config->get('add_js_defer'),
      '#description' => t('If is checked, the <a href="@url">defer attribute</a> is set on the &lt;script&gt; tag (default: unchecked).', array('@url' => 'http://www.w3.org/TR/html40/interact/scripts.html#h-18.2.1')),
    );

    $form['advanced_settings']['add_css_media'] = array(
      '#type' => 'select',
      '#title' => t('Prettify CSS media type'),
      '#options' => array('all' => t('All'), 'print' => t('Print'), 'screen' => t('Screen')),
      '#default_value' => $config->get('add_css_media'),
      '#description' => t('The media type for the Prettify stylesheet (default: All).'),
    );

    $form['advanced_settings']['add_css_preprocess'] = array(
      '#type' => 'checkbox',
      '#title' => t('Prettify CSS preprocess'),
      '#default_value' => $config->get('add_css_preprocess'),
      '#description' => t('If is checked, Prettify CSS file be aggregated and compressed if this feature has been turned on under the performance section (default: checked).'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
    // Force to check <pre><code> tag if <pre> tag is checked.
    if (!empty($form_state['values']['auto_tags'][PRETTIFY_MARKUP_PRE])) {
      $form_state['values']['auto_tags'][PRETTIFY_MARKUP_PRECODE] = PRETTIFY_MARKUP_PRECODE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $values = $form_state['values'];
    \Drupal::config('prettify.settings')
    ->set('activation_mode', $values['activation_mode'])
    ->set('activation_pages', $values['activation_pages'])
    ->set('add_css_media', $values['add_css_media'])
    ->set('add_css_preprocess', $values['add_css_preprocess'])
    ->set('add_js_defer', $values['add_js_defer'])
    ->set('add_js_preprocess', $values['add_js_preprocess'])
    ->set('add_js_scope', $values['add_js_scope'])
    #->set('auto', $values['auto'])
    ->set('auto_custom_markup', $values['auto_custom_markup'])
    ->set('auto_disabled_class', $values['auto_disabled_class'])
    ->set('auto_element_class', $values['auto_element_class'])
    ->set('auto_markup', $values['auto_markup'])
    ->set('behaviour_extensions', $values['behaviour_extensions'])
    ->set('behaviour_linenums', $values['behaviour_linenums'])
    ->set('css', $values['css'])
    ->set('css_path', $values['css_path'])
    ->save();

    parent::submitForm($form, $form_state);
  }

}


function _prettify_get_options_lang_extensions() {
  return array('apollo' => t('AGC/AEA Assembly'), 'css' => t('CSS'), 'hs' => t('Haskell'), 'lisp' => t('Common Lisp and similar languages'), 'lua' => t('Lua'), 'ml' => t('OCaml, SML, F# and similar languages'), 'proto' => t('Protocol Buffers (<a href="@url">code.google.com/apis/protocolbuffers</a>)', array('@url' => url('http://code.google.com/apis/protocolbuffers'))), 'scala' => t('Scala'), 'sql' => t('SQL'), 'vb' => t('Visual Basic'), 'vhdl' => t("VHDL '93"), 'wiki' => t('WikiText'), 'yaml' => t('YAML'));
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

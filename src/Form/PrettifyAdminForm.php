<?php

/**
 * @file
 * Contains \Drupal\prettify\Form\PrettifyAdminForm.
 */

namespace Drupal\prettify\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\prettify\Plugin\Filter\PrettifyCode;

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
  protected function getEditableConfigNames() {
    return [
      'prettify.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('prettify.settings');

    $library = \Drupal::service('library.discovery')->getLibraryByName('prettify', 'prettify');
    if (!file_exists(DRUPAL_ROOT . '/' . $library['js'][0]['data'])) {
      drupal_set_message($this->t('Could not find Google JavaScript code prettifier library. Check the <a href="@status">status report</a> for more information.', ['@status' => Url::fromRoute('system.status')]), 'error');
    }

    $form['settings'] = [
      '#type' => 'vertical_tabs',
      '#attached' => [
        'library' => [
          'prettify/admin',
        ]
      ],
    ];

    // Elements and classes.

    $form['auto'] = [
      '#type' => 'details',
      '#title' => $this->t('Elements and classes'),
      '#open' => TRUE,
      '#group' => 'settings',
    ];

    $form['auto']['auto_markup'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Markup for code blocks'),
      '#description' => $this->t("Choose which DOM elements containing source code to highlight. The language of the code will be auto-detected for Prettify."),
      '#default_value' => $config->get('auto_markup'),
      '#options' => [
        PrettifyCode::PRETTIFY_MARKUP_CODE => '<code>' . Html::escape('<code>...</code>') . '</code>',
        PrettifyCode::PRETTIFY_MARKUP_PRE => '<code>' . Html::escape('<pre>...</pre>') . '</code>',
        PrettifyCode::PRETTIFY_MARKUP_PRECODE => '<code>' . Html::escape('<pre><code>...</code></pre>') . '</code>',
      ],
    ];

    $description = $this->t("Constrains syntax highlighting to within the bounds of the specified element - can be a DOM element or a jQuery selector, like 'div.node-type-story, .block .content'. Empty, for  document element.");
    $form['auto']['auto_element_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Containment element'),
      '#default_value' => $config->get('auto_element_class'),
      '#description' => $description,
    ];

    $form['auto']['auto_disabled_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Class to disable highlighting'),
      '#default_value' => $config->get('auto_disabled_class'),
      '#description' => $this->t('Use this class to disable highlighting of a fragment, like &lt;pre class="nocode"&gt;...&lt;/pre&gt;.')
    ];

    $form['auto']['auto_custom_markup'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom markup for code blocks'),
      '#default_value' => $config->get('auto_custom_markup'),
      '#description' => $this->t('If you use different markup for code blocks you can use jQuery selectors to select elements containing the code to highlight. Enter one jQuery selector per line, example selectors are %selector1 or %selector2.', ['%selector1' => '.node .field-field-source-code pre', '%selector2' => 'pre']),
      '#rows' => 3,
    ];

    // Styles and languages.

    $form['global'] = [
      '#type' => 'details',
      '#title' => $this->t('Styles and languages'),
      '#open' => TRUE,
      '#group' => 'settings',
    ];

    $form['global']['css'] = [
      '#type' => 'radios',
      '#title' => $this->t('Styles'),
      '#default_value' => $config->get('css'),
      '#options' => $this->getOptionsStyles() + ['custom' => $this->t('Custom CSS')],
      '#description' => $this->t('Defines the stylesheets to be used. View <a href="@url">Prettify Themes Gallery</a>.', ['@url' => Url::fromUri('https://rawgit.com/google/code-prettify/master/styles/index.html')]),
    ];

    $form['global']['css_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom CSS path'),
      '#default_value' => $config->get('css_path'),
      '#description' => $this->t('If "Define CSS" was selected above, enter external URL or path to a CSS file. Available tokens: <code>%b</code> (base path, eg: <code>/</code>), <code>%t</code> (path to theme, eg: <code>themes/garland</code>)') . '<br />' . $this->t('Example:') . ' css/prettify.css,/themes/garland/prettify.css,%b%t/prettify.css,http://example.com/external.css',
      '#states' => [
        'visible' => [
          ':input[name="css"]' => ['value' => 'custom'],
        ],
      ],
    ];

    $form['global']['behaviour_linenums'] = [
      '#type' => 'select',
      '#title' => $this->t('Show line numbers'),
      '#description' => $this->t('Turn on line numbering by default.'),
      '#default_value' => $config->get('behaviour_linenums'),
      '#options' => [$this->t('No'), $this->t('Yes')],
    ];

    $behaviour_extensions = $config->get('behaviour_extensions');
    $form['global']['behaviour_extensions'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Optionally language extensions'),
      '#description' => $this->t("Because of commenting conventions, Prettify doesn't work on Smalltalk, Lisp-like, or CAML-like languages without an explicit lang class."),
      '#default_value' => $behaviour_extensions ? $behaviour_extensions : [],
      '#options' => $this->getOptionsLangExtensions(),
    ];

    // Pages.

    $form['page_load_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Pages'),
      '#open' => TRUE,
      '#group' => 'settings',
    ];

    $form['page_load_settings']['activation_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Load Prettify on specific pages'),
      '#options' => [
        $this->t('Load on every page except the listed pages.'),
        $this->t('Load on only the listed pages.'),
      ],
      '#default_value' => $config->get('activation_mode'),
    ];

    $form['page_load_settings']['activation_pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Pages'),
      '#default_value' => $config->get('activation_pages'),
      '#description' => $this->t("Enter one page per line as Drupal paths. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", [
        '%blog' => '/blog',
        '%blog-wildcard' => '/blog/*',
        '%front' => '<front>',
      ]),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('prettify.settings')
      ->set('activation_mode', $form_state->getValue('activation_mode'))
      ->set('activation_pages', $form_state->getValue('activation_pages'))
      ->set('auto_custom_markup', $form_state->getValue('auto_custom_markup'))
      ->set('auto_disabled_class', $form_state->getValue('auto_disabled_class'))
      ->set('auto_element_class', $form_state->getValue('auto_element_class'))
      ->set('auto_markup', $form_state->getValue('auto_markup'))
      ->set('behaviour_extensions', $form_state->getValue('behaviour_extensions'))
      ->set('behaviour_linenums', $form_state->getValue('behaviour_linenums'))
      ->set('css', $form_state->getValue('css'))
      ->set('css_path', $form_state->getValue('css_path'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  private function getOptionsLangExtensions() {
    return [
      'apollo' => $this->t('AGC/AEA Assembly'),
      'basic' => $this->t('Basic'),
      'clj' => $this->t('Clojure'),
      'css' => $this->t('CSS'),
      'dart' => $this->t('Dart'),
      'erlang' => $this->t('Erlang'),
      'ex' => $this->t('Elixir'),
      'go' => $this->t('Go'),
      'hs' => $this->t('Haskell'),
      'lasso' => $this->t('Lasso'),
      'lisp' => $this->t('Common Lisp and related languages'),
      'llvm' => $this->t('LLVM'),
      'logtalk' => $this->t('Logtalk'),
      'lua' => $this->t('Lua'),
      'matlab' => $this->t('MATLAB'),
      'ml' => $this->t('OCaml, SML, F# and similar languages'),
      'mumps' => $this->t('MUMPS'),
      'n' => $this->t('Nemerle language'),
      'pascal' => $this->t('(Turbo) Pascal'),
      'proto' => $this->t('Protocol Buffers (<a href="@url">code.google.com/apis/protocolbuffers</a>)', ['@url' => Url::fromUri('http://code.google.com/apis/protocolbuffers')]),
      'r' => $this->t('S, S-plus, and R source code'),
      'rd' => $this->t('R documentation (Rd) files'),
      'rust' => $this->t('Rust'),
      'scala' => $this->t('Scala'),
      'sql' => $this->t('SQL'),
      'swift' => $this->t('Swift'),
      'tcl' => $this->t('TCL'),
      'tex' => $this->t('Tex'),
      'vb' => $this->t('Visual Basic'),
      'vhdl' => $this->t("VHDL '93"),
      'wiki' => $this->t('WikiText'),
      'xq' => $this->t('XQuery'),
      'yaml' => $this->t('YAML')
    ];
  }

  private function getOptionsStyles() {
    return [
      'default' => $this->t('Default'),
      'desert' => $this->t('Desert vim theme (by <a href="@url">anatoly techtonik</a>)', ['@url' => 'https://code.google.com/u/techtonik@gmail.com/']),
      'sunburst' => $this->t('Sunburst vim theme (by David Leibovic)'),
      'sons-of-obsidian' => $this->t('Sons of Obsidian theme (by <a href="@url">Alex Ford</a>)', ['@url' => 'http://codetunnel.com/blog/post/71/google-code-prettify-obsidian-theme']),
      'doxy' => $this->t('Doxy (by by Robert Sperberg)'),
    ];
  }

}

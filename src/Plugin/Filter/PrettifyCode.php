<?php

/**
 * @file
 * Contains \Drupal\prettify\Plugin\Filter\PrettifyCode.
 */

namespace Drupal\prettify\Plugin\Filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to limit allowed HTML tags.
 *
 * @Filter(
 *   id = "filter_prettify",
 *   module = "prettify",
 *   title = @Translation("Source code prettifier"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 *   settings = {
 *     "prettify_filter_tag" = "<code> <source>",
 *   }
 * )
 */
class PrettifyCode extends FilterBase {

  /**
   * Google Code Prettify library name.
   */
  const PRETTIFY_HTML_CLASS = 'prettyprint';

  /**
   * Markup identifier for <code>...</code> blocks.
   */
  const PRETTIFY_MARKUP_CODE = 'code';

  /**
   * Markup identifier for <pre>...</pre> blocks.
   */
  const PRETTIFY_MARKUP_PRE = 'pre';

  /**
   * Markup identifier for <pre><code>...</code></pre> blocks.
   */
  const PRETTIFY_MARKUP_PRECODE = 'precode';

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['prettify_filter_tag'] = [
      '#type' => 'textfield',
      '#title' => t('Code snippet tags'),
      '#default_value' => $this->settings['prettify_filter_tag'],
      '#maxlength' => 1024,
      '#description' => t('Code snippets in this tags will automatically be pretty printed.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $text = preg_replace_callback('@(?:<p>\s*)?\[prettify(.*?)\](.+?)\[/prettify\](?:\s*</p>)?@s', '_prettify_process_callback', $text);
    return new FilterProcessResult($text);
  }

  /**
   * {@inheritdoc}
   */
  public function prepare($text, $langcode) {
    $prettify_tags = preg_split('/\s+|<|>/', $this->settings['prettify_filter_tag'], -1, PREG_SPLIT_NO_EMPTY);
    foreach ($prettify_tags as $tag) {
      $tag = preg_quote($tag, '@');
      $text = preg_replace_callback("@\<$tag(?:\s+(.+?))?\>(.+?)\</$tag\>@s", '_prettify_escape_callback', $text);
    }
    return $text;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    $code_tags = preg_split('/\s+|<|>/', $this->settings['prettify_filter_tag'], -1, PREG_SPLIT_NO_EMPTY);
    if ($long) {
      return t('To post highlighted source code snippets, surround them with &lt;pre&gt;...&lt;/pre&gt;, &lt;pre&gt;&lt;code&gt;...&lt;/pre&gt;&lt;/code&gt; or &lt;code&gt;...&lt;/code&gt; tags.');
    }
    else {
      $help = '';
      for ($i = 0; $i < count($code_tags); $i++) {
        $tag = $code_tags[$i];
        $help .= "<$tag>...</$tag>";
        if ($i < count($code_tags) - 2) {
          $help .= ', ';
        }
        elseif ($i < count($code_tags) - 1) {
          $help .= ' or ';
        }
      }
      return t('Code snippets in @tags tags automatically will be pretty printed.', ['@tags' => $help]);
    }
  }
}

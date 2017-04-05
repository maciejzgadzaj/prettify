<?php

/**
 * @file
 * Contains \Drupal\prettify\Plugin\Filter\PrettifyCode.
 */

namespace Drupal\prettify\Plugin\Filter;

use Drupal\filter\Annotation\Filter;
use Drupal\Core\Annotation\Translation;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to limit allowed HTML tags.
 *
 * @Filter(
 *   id = "prettify",
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
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state) {
    $form['prettify_filter_tag'] = array(
      '#type' => 'textfield',
      '#title' => t('Code snippets tags'),
      '#default_value' => $this->settings['prettify_filter_tag'],
      '#maxlength' => 1024,
      //'#description' => t('A list of HTML tags that can be used. JavaScript event attributes, JavaScript URLs, and CSS are always stripped.'),
      '#description' => t('Code snippets in this tags will automatically be pretty printed.'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode, $cache, $cache_id) {
    $text = preg_replace_callback('@(?:<p>\s*)?\[prettify(.*?)\](.+?)\[/prettify\](?:\s*</p>)?@s', '_prettify_process_callback', $text);
    return $text;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare($text, $langcode, $cache, $cache_id) {
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
    global $base_url;

    $code_tags = preg_split('/\s+|<|>/', $this->settings['prettify_filter_tag'], -1, PREG_SPLIT_NO_EMPTY);
    if ($long) {
      return t('To post highlighted source code snippets, surround them with &lt;pre&gt;...&lt;/pre&gt;, &lt;pre&gt;&lt;code&gt;...&lt;/pre&gt;&lt;/code&gt; or &lt;code&gt;...&lt;/code&gt; tags.');
    }
    else {
      $help = '';
      for ($i = 0; $i < count($code_tags); $i++) {
        $tag = $code_tags[$i];
        $help .= "&lt;$tag&gt;...&lt;/$tag&gt;";
        if ($i < count($code_tags) - 2) {
          $help .= ', ';
        }
        elseif ($i < count($code_tags) - 1) {
          $help .= ' or ';
        }
      }
      return t('Code snippets in !tags automatically will be pretty printed.', array('!tags' => $help));
      //return t("You may post code snippets using &lt;pre&gt;...&lt;/pre&gt;, &lt;pre&gt;&lt;code&gt;...&lt;/pre&gt;&lt;/code&gt; or &lt;code&gt;...&lt;/code&gt; tags.");
    }
  }
}

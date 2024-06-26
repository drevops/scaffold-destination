<?php

namespace Drupal\search_api\Plugin\search_api\processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Plugin\search_api\processor\Resources\Porter2;
use Drupal\search_api\Processor\FieldsProcessorPluginBase;
use Drupal\search_api\Query\QueryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Stems search terms.
 *
 * @SearchApiProcessor(
 *   id = "stemmer",
 *   label = @Translation("Stemmer"),
 *   description = @Translation("Stems search terms (for example, <em>talking</em> to <em>talk</em>). Currently, this only acts on English language content. It uses the Porter 2 stemmer algorithm (<a href=""https://wikipedia.org/wiki/Stemming"">More information</a>). For best results, use after tokenizing."),
 *   stages = {
 *     "pre_index_save" = 0,
 *     "preprocess_index" = 0,
 *     "preprocess_query" = 0,
 *   }
 * )
 */
class Stemmer extends FieldsProcessorPluginBase {

  /**
   * Static cache for already-generated stems.
   *
   * @var string[]
   */
  protected $stems = [];

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|null
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function supportsIndex(IndexInterface $index): bool {
    $languages = \Drupal::languageManager()->getLanguages();
    // Make processor available only if English is one of the site languages.
    foreach ($languages as $language) {
      if (static::isEnglish($language->getId())) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $plugin = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $plugin->setLanguageManager($container->get('language_manager'));
    return $plugin;
  }

  /**
   * Checks whether the given language code represents a variation of English.
   *
   * @param string $langcode
   *   An ISO 639-1 language code or IETF language tag.
   *
   * @return bool
   *   TRUE if the language code represents a variation of English, FALSE
   *   otherwise.
   */
  protected static function isEnglish(string $langcode): bool {
    return str_starts_with($langcode, 'en');
  }

  /**
   * Retrieves the language manager.
   *
   * @return \Drupal\Core\Language\LanguageManagerInterface
   *   The language manager.
   */
  public function getLanguageManager(): LanguageManagerInterface {
    return $this->languageManager ?: \Drupal::service('language_manager');
  }

  /**
   * Sets the language manager.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The new language manager.
   *
   * @return $this
   */
  public function setLanguageManager(LanguageManagerInterface $language_manager): self {
    $this->languageManager = $language_manager;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();

    $configuration += [
      'exceptions' => [
        // cspell:disable
        'texan' => 'texa',
        'mexican' => 'mexic',
        // cspell:enable
      ],
    ];

    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $description = $this->t('If the <a href="http://snowball.tartarus.org/algorithms/english/stemmer.html">algorithm</a> does not stem words in your dataset in the desired way, you can enter specific exceptions in the form of WORD=STEM, where "WORD" is the original word in the text and "STEM" is the resulting stem. List each exception on a separate line.');

    // Convert the keyed array into a config format (word=stem)
    $default_value = http_build_query($this->configuration['exceptions'], '', "\n");

    $form['exceptions'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Exceptions'),
      '#description' => $description,
      '#default_value' => $default_value,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);

    $exceptions = $form_state->getValue('exceptions');
    if (($parsed = parse_ini_string($exceptions)) === FALSE) {
      $el = $form['exceptions'];
      $form_state->setError($el, $el['#title'] . ': ' . $this->t('The entered text is not in valid WORD=STEM format.'));
    }
    else {
      $form_state->setValue('exceptions', $parsed);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessIndexItems(array $items) {
    foreach ($items as $item) {
      // Limit this processor to English language data. If the site only has
      // English languages enabled, we assume all content is English.
      if (!static::isEnglish($item->getLanguage())
          && !$this->isSiteEnglishOnly()) {
        continue;
      }
      foreach ($item->getFields() as $name => $field) {
        if ($this->testField($name, $field)) {
          $this->processField($field);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preprocessSearchQuery(QueryInterface $query) {
    // Only process queries that can (also) return English language content. If
    // the site only has English languages enabled, we assume all content is
    // English.
    if ($query->getLanguages() !== NULL && !$this->isSiteEnglishOnly()) {
      $has_english = FALSE;
      foreach ($query->getLanguages() as $langcode) {
        if (static::isEnglish($langcode)) {
          $has_english = TRUE;
          break;
        }
      }
      if (!$has_english) {
        return;
      }
    }
    parent::preprocessSearchQuery($query);
  }

  /**
   * {@inheritdoc}
   */
  protected function testType($type) {
    return $this->getDataTypeHelper()->isTextType($type);
  }

  /**
   * {@inheritdoc}
   */
  protected function process(&$value) {
    // In the absence of the tokenizer processor, this ensures split words.
    $words = preg_split('/[^\p{L}\p{N}]+/u', strip_tags($value), -1, PREG_SPLIT_NO_EMPTY);
    $stemmed = [];
    foreach ($words as $word) {
      // To optimize processing, store processed stems in a static array.
      if (!isset($this->stems[$word])) {
        $stem = new Porter2($word, $this->configuration['exceptions']);
        $this->stems[$word] = $stem->stem();
      }
      $stemmed[] = $this->stems[$word];
    }
    $value = implode(' ', $stemmed);
  }

  /**
   * Tests whether this site only has English languages enabled.
   *
   * @return bool
   *   TRUE if all enabled languages are variations of English.
   */
  protected function isSiteEnglishOnly(): bool {
    $langcodes = array_keys($this->getLanguageManager()->getLanguages());
    foreach ($langcodes as $langcode) {
      if (!static::isEnglish($langcode)) {
        return FALSE;
      }
    }
    return TRUE;
  }

}

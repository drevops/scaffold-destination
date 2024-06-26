<?php

declare(strict_types=1);

namespace Drupal\Tests\language\Kernel\Migrate\d6;

use Drupal\language\Entity\ContentLanguageSettings;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Tests\migrate_drupal\Kernel\d6\MigrateDrupal6TestBase;

/**
 * Tests migration of i18ntaxonomy vocabulary settings.
 *
 * @group migrate_drupal_6
 */
class MigrateLanguageContentTaxonomyVocabularySettingsTest extends MigrateDrupal6TestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'language',
    'content_translation',
    'taxonomy',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('taxonomy_term');
    $this->executeMigrations([
      'language',
      'd6_taxonomy_vocabulary',
      'd6_language_content_taxonomy_vocabulary_settings',
    ]);
  }

  /**
   * Tests migration of 18ntaxonomy vocabulary settings.
   */
  public function testLanguageContentTaxonomy(): void {
    $target_entity = 'taxonomy_term';
    // Per Language.
    $this->assertLanguageContentSettings($target_entity, 'vocabulary_1_i_0_', LanguageInterface::LANGCODE_SITE_DEFAULT, TRUE, ['enabled' => FALSE]);
    // Set language to vocabulary.
    $this->assertLanguageContentSettings($target_entity, 'vocabulary_2_i_1_', 'fr', FALSE, ['enabled' => FALSE]);
    // Localize terms.
    $this->assertLanguageContentSettings($target_entity, 'vocabulary_3_i_2_', LanguageInterface::LANGCODE_SITE_DEFAULT, TRUE, ['enabled' => FALSE]);
    // None translation enabled.
    $this->assertLanguageContentSettings($target_entity, 'vocabulary_name_much_longer_th', LanguageInterface::LANGCODE_SITE_DEFAULT, TRUE, ['enabled' => TRUE]);
    $this->assertLanguageContentSettings($target_entity, 'tags', LanguageInterface::LANGCODE_SITE_DEFAULT, FALSE, ['enabled' => FALSE]);
    $this->assertLanguageContentSettings($target_entity, 'forums', LanguageInterface::LANGCODE_SITE_DEFAULT, FALSE, ['enabled' => FALSE]);
    $this->assertLanguageContentSettings($target_entity, 'type', LanguageInterface::LANGCODE_SITE_DEFAULT, FALSE, ['enabled' => FALSE]);
  }

  /**
   * Asserts a content language settings configuration.
   *
   * @param string $target_entity
   *   The expected target entity type.
   * @param string $bundle
   *   The expected bundle.
   * @param string $default_langcode
   *   The default language code.
   * @param bool $language_alterable
   *   The expected state of language alterable.
   * @param array $third_party_settings
   *   The content translation setting.
   *
   * @internal
   */
  public function assertLanguageContentSettings(string $target_entity, string $bundle, string $default_langcode, bool $language_alterable, array $third_party_settings): void {
    $config = ContentLanguageSettings::load($target_entity . "." . $bundle);
    $this->assertInstanceOf(ContentLanguageSettings::class, $config);
    $this->assertSame($target_entity, $config->getTargetEntityTypeId());
    $this->assertSame($bundle, $config->getTargetBundle());
    $this->assertSame($default_langcode, $config->getDefaultLangcode());
    $this->assertSame($language_alterable, $config->isLanguageAlterable());
    $this->assertSame($third_party_settings, $config->getThirdPartySettings('content_translation'));
  }

}

<?php

namespace Drupal\Core\DependencyInjection\Compiler;

use Drupal\Core\Cache\NullBackendFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Defines a compiler pass to register development settings.
 */
class DevelopmentSettingsPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    /** @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface $development_settings */
    $development_settings = $container->get('keyvalue')->get('development_settings');
    $twig_debug = $development_settings->get('twig_debug', FALSE);
    $twig_cache_disable = $development_settings->get('twig_cache_disable', FALSE);
    if ($twig_debug || $twig_cache_disable) {
      $twig_config = $container->getParameter('twig.config');
      $twig_config['debug'] = $twig_debug;
      $twig_config['cache'] = !$twig_cache_disable;
      $container->setParameter('twig.config', $twig_config);
    }

    if ($development_settings->get('disable_rendered_output_cache_bins', FALSE)) {
      $cache_bins = ['page', 'dynamic_page_cache', 'render'];
      if (!$container->hasDefinition('cache.backend.null')) {
        $container->register('cache.backend.null', NullBackendFactory::class);
      }
      foreach ($cache_bins as $cache_bin) {
        if ($container->has("cache.$cache_bin")) {
          $container->getDefinition("cache.$cache_bin")
            ->clearTag('cache.bin')
            ->addTag('cache.bin', ['default_backend' => 'cache.backend.null']);
        }
      }
    }
  }

}

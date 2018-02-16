<?php

namespace Drupal\fusion_tax_exempt;

use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;

class FusionTaxExemptServiceProvider extends ServiceProviderBase implements ServiceProviderInterface {
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('commerce_tax.tax_order_processor');
    $definition->setClass('Drupal\fusion_tax_exempt\FusionTaxExemptController\FusionTaxExemptController');
  }
}

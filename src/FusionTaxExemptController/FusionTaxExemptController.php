<?php
/**
 * @file
 * Contains \Drupal\fusion_tax_exempt\FusionTaxExemptController.
 */
namespace Drupal\fusion_tax_exempt\FusionTaxExemptController;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

class FusionTaxExemptController implements OrderProcessorInterface {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new TaxOrderProcessor object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
  }

  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order) {
    $user = $this->currentUser;
    // check if the user has the field
    if ($user->field_tax_exempt->value == 1) {
      //Drupal\commerce_tax\TaxOrderProcessor
      $tax_type_storage = $this->entityTypeManager->getStorage('commerce_tax_type');
      /** @var \Drupal\commerce_tax\Entity\TaxTypeInterface[] $tax_types */
      $tax_types = $tax_type_storage->loadMultiple();
      foreach ($tax_types as $tax_type) {
        if ($tax_type->status() && $tax_type->getPlugin()->applies($order)) {
          //do notthing if user is tax exempt.
        }
      }
    }
    else {
      //Drupal\commerce_tax\TaxOrderProcessor
      $tax_type_storage = $this->entityTypeManager->getStorage('commerce_tax_type');
      /** @var \Drupal\commerce_tax\Entity\TaxTypeInterface[] $tax_types */
      $tax_types = $tax_type_storage->loadMultiple();
      foreach ($tax_types as $tax_type) {
        if ($tax_type->status() && $tax_type->getPlugin()->applies($order)) {
          $tax_type->getPlugin()->apply($order);
        }
      }
      if ($order->getStore()->get('prices_include_tax')->value) {
        foreach ($order->getItems() as $order_item) {
          $adjustments = $order_item->getAdjustments();
          $negative_tax_adjustments = array_filter($adjustments, function ($adjustment) {
            /** @var \Drupal\commerce_order\Adjustment $adjustment */
            return $adjustment->getType() == 'tax' && $adjustment->isNegative();
          });
          $adjustments = array_diff_key($adjustments, $negative_tax_adjustments);
          $unit_price = $order_item->getUnitPrice();
          foreach ($negative_tax_adjustments as $adjustment) {
            $unit_price = $unit_price;
          }
          $order_item->setUnitPrice($unit_price);
          $order_item->setAdjustments($adjustments);
        }
      }
    }

  }
}

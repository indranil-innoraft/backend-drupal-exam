<?php

namespace Drupal\like_button\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a simple like button block.
 *
 * @Block(
 *   id = "like_button_simple_like_button",
 *   admin_label = @Translation("Simple Like button"),
 *   category = @Translation("Custom"),
 * )
 */
final class SimpleLikeButtonBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Get the like button.
    $like_button = \Drupal::formBuilder()->getForm(\Drupal\like_button\Form\LikeForm::class);

    // Build block.
    $theme_vars = [
      'like_button' => $like_button,
    ];
    return [
      '#theme' => 'block_like_button',
      '#vars' => $theme_vars,
    ];
  }

}

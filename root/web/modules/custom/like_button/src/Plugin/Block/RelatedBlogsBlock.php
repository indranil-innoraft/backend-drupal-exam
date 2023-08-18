<?php

namespace Drupal\like_button\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a related blogs block.
 *
 * @Block(
 *   id = "like_button_related_blogs",
 *   admin_label = @Translation("Related Blogs"),
 *   category = @Translation("Custom"),
 * )
 */
final class RelatedBlogsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $id = \Drupal::currentUser()->id();
    $query = \Drupal::database()->select('like_button');
    $query->fields('like_button', ['like_count', 'entity_id']);
    $query->condition('like_button.user_id', $id);
    $data = $query->execute()->fetchAll();
    $max = 0;
    $temp = 0;
    $entity_id = [];
    foreach ($data as $d) {
      if ($temp > $max) {
        $max = (int) $d->like_count;
        array_push($entity_id, (int) ($d->entity_id));
      }
      else {
        $temp = (int) $d->like_count;
      }
    }

    $node_1 = Node::load($entity_id[0]);
    $title_field_1 = $node_1->getTitle();
    $node_2 = Node::load($entity_id[1]);
    $title_field_2 = $node_2->getTitle();
    $node_3 = Node::load($entity_id[2]);
    $title_field_3 = $node_3->getTitle();

    $build['content'] = [
      '#markup' => t('@blog_1, @blog_2, @blog_3', [
        '@blog_1' => $title_field_1,
        '@blog_2' => $title_field_2,
        '@blog_3' => $title_field_3,
      ]),
      '#cache' => [
        'tags' => [
          'like_count',
        ],
      ],
    ];
    return $build;
  }

}

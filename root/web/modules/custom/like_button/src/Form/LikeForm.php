<?php declare(strict_types = 1);

namespace Drupal\like_button\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Like Button form.
 */
final class LikeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'like_button_like';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $current_entity = $this->getCurrentEntity();
    $entity = $current_entity->getEntityTypeId();
    $bundle = $current_entity->bundle();
    $entity_id = $current_entity->id();
    $uid = \Drupal::currentUser()->id();
    $button_text = $this->getLikeCount($entity, $entity_id, $bundle);
    $form['entity'] = [
      '#type' => 'hidden',
      '#required' => true,
      '#default_value' => $entity,
    ];
    $form['entity_id'] = [
      '#type' => 'hidden',
      '#required' => true,
      '#default_value' => $entity_id,
    ];
    $form['bundle'] = [
      '#type' => 'hidden',
      '#required' => true,
      '#default_value' => $bundle,
    ];
    $form['count'] = [
      '#type' => 'markup',
      '#markup' => t('Like Count: @count', ['@count' => $button_text]),
    ];
    $form['actions'] = [
      '#prefix' => '<span class="'.$entity.$entity_id .' '.'"> ',
      '#type' => 'submit',
      '#submit' => ['::submitLikeAjax'],
      '#attributes' => array('class' => array($entity.$entity_id)),
      '#value' => 'Like',
      '#suffix' => '</span>'
    ];

    $form['#cache'] =  [
      'tags' =>  [
        'like-count',
      ],
    ];

    $form['#attached']['library'][] = 'like_button/like';
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
  }

  /**
   * Ajax callback to validate the email field.
   */
  public function submitLikeAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $uid = \Drupal::currentUser()->id() ?? 0;
    $entity = $form_state->getValue('entity');
    $entity_id = $form_state->getValue('entity_id');
    $bundle = $form_state->getValue('bundle');
    $like_count = $this->getLikeCount($entity, $entity_id, $bundle);

    $data = [
      'user_id' => $uid,
      'bundle' => $bundle,
      'entity_id' => $entity_id,
      'like_count' => $like_count + 1,
    ];
    $query = \Drupal::service('database')->insert('like_button')->fields(['user_id', 'bundle', 'entity_id', 'like_count']);
    $query->values($data);
    $query->execute();
    Cache::invalidateTags(['like-count']);
    return $response;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }

  /**
   * @param $entity
   * @param $entity_id
   * @param $bundle
   * @return mixed
   */
  private function getLikeCount($entity, $entity_id, $bundle){
    $query = \Drupal::database()->select('like_button');
    $query->addField('like_button', 'id');
    $query->condition('like_button.entity_id', $entity_id);
    $query->condition('like_button.bundle', $bundle);
    return $query->countQuery()->execute()->fetchField();
  }

  /**
   * Get current entity, if any.
   */
  private function getCurrentEntity(){
    $currentRouteParameters = \Drupal::routeMatch()->getParameters();
    foreach ($currentRouteParameters as $param) {
      if ($param instanceof \Drupal\Core\Entity\EntityInterface) {
        $entity = $param;
        return $entity;
      }
    }
    return NULL;
  }


}

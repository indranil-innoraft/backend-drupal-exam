<?php

namespace Drupal\blogger_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Returns responses for Blogger_registration routes.
 */
class BloggerRegistrationController extends ControllerBase {

  /**
   * This is entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * This is a constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   This will be used to fetch the nodes.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Fetching all blogs nodes data.
   *
   * @param Request $request
   *  To get the current request.
   *
   * @return JsonResponse
   *  If the header is correct it will return a json response.
   */
  public function fetchBlogData(Request $request) {
    $headers = $request->headers->get('api-key');
    if ($headers === '123x4') {
      $nodes = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', 'blogs')
        ->accessCheck(FALSE);
      $nodes = $nodes->execute();
      $data = $this->entityTypeManager()->getStorage('node')->loadMultiple($nodes);

      $json_array = [
        'title' => 'blogs',
        'data' => [],
      ];
      foreach($data as $d) {
        $term_item = $d->field__blog_tags->getValue();
        $term = array_pop($term_item);
        $tid = $term['target_id'];
        $taxonomy_term = $this->entityTypeManager()->getStorage('taxonomy_term')->load($tid);
        $json_array['data'][] = [
          'id' => $d->id(),
          'title' => $d->get('title')->value,
          'author' => $d->get('uid')->value,
          'body' => $d->get('body')->value,
          'publish_date' => $d->get('field_published_date')->value,
          'tags' => $taxonomy_term->getName(),
        ];
      }
      return new JsonResponse($json_array);
    }
    else {
      throw new AccessDeniedHttpException();
    }
  }

  /**
   * Fetching all blogs nodes data based on time range.
   *
   * @param string $date_form
   *  Starting date to search.
   * @param string $date_to
   *  End date to search.
   * @param Request $request
   *  To get the current request.
   *
   * @return JsonResponse
   *  If the header is correct it will return a json response.
   */
  public function getJsonResposnSpecificDate($date_form, $date_to, Request $request) {
    $headers = $request->headers->get('api-key');
    if ($headers === '123x4') {
      $nodes = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', 'blogs')
        ->condition('field_published_date', $date_form, '>=')
        ->condition('field_published_date', $date_to, '<=')
        ->accessCheck(FALSE);
      $nodes = $nodes->execute();
      $data = $this->entityTypeManager->getStorage('node')->loadMultiple($nodes);
      $json_array = [
        'type' => 'blogs',
        'date-form' => $date_form,
        'date-to' => $date_to,
        'data' => [],
      ];
      foreach($data as $d) {
        $term_item = $d->field__blog_tags->getValue();
        $term = array_pop($term_item);
        $tid = $term['target_id'];
        $taxonomy_term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
        $json_array['data'][] = [
          'id' => $d->id(),
          'title' => $d->get('title')->value,
          'body' => $d->get('body')->value,
          'publish_date' => $d->get('field_published_date')->value,
          'tags' => $taxonomy_term->getName(),

        ];
      }
      return new JsonResponse($json_array);
    }
    else {
      throw new AccessDeniedHttpException();
    }
  }

  /**
   * Fetching all blogs nodes based on tags.
   *
   * @param string $tag
   *  Search tag.
   * @param Request $request
   *  To get the current request.
   *
   * @return JsonResponse
   *   If the header is correct it will return a json response.
   */
  public function getJsonResposnSpecificTags($tag, Request $request) {
    $headers = $request->headers->get('api-key');
    if ($headers === '123x4') {
      $nodes = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', 'blogs')
        ->condition('field__blog_tags', $tag, '=')
        ->accessCheck(FALSE);
      $nodes = $nodes->execute();
      $data = $this->entityTypeManager()->getStorage('node')->loadMultiple($nodes);
      $json_array = [
        'type' => 'blogs',
        'tag' => $tag,
        'data' => [],
      ];
      foreach ($data as $d) {
        $term_item = $d->field__blog_tags->getValue();
        $term = array_pop($term_item);
        $tid = $term['target_id'];
        $supplement = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
        $json_array['data'][] = [
          'id' => $d->id(),
          'title' => $d->get('title')->value,
          'body' => $d->get('body')->value,
          'publish_date' => $d->get('field_published_date')->value,
          'tags' => $supplement->getName(),
        ];
      }
      return new JsonResponse($json_array);
    }
    else {
      throw new AccessDeniedHttpException();
    }
  }

}

<?php

namespace Drupal\image_lightgallery_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use ReflectionClass;
use ReflectionMethod;

/**
 * Plugin implementation of the 'image_lightgallery_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "image_lightgallery_formatter",
 *   label = @Translation("Image lightgallery formatter"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImageLightgalleryFormatter extends ImageFormatter implements ContainerFactoryPluginInterface {

  /** There's probably a better way to do this **/
  protected $template = '';


  public function viewElements(FieldItemListInterface $items, $langcode) {

    $children = parent::viewElements($items, $langcode);

    $numOfChildren = count($children);

    if ($numOfChildren === 0) {
      return $children;
    }

    /** get image URL (should be a simpler method no?!?! */
    $files = $this->getEntitiesToView($items, $langcode);
    foreach ($files as $delta => $file) {
      $image_uri = $file->getFileUri();
      $url = file_create_url($image_uri);
      $children[$delta]['#__url'] = $url;
    }

    for ($i = 0; $i < $numOfChildren; ++$i) {
      $children[$i] = $this->modifyChild($children[$i]);
    }

    $elements = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lightgallery'],
      ],
      '#children' => $children
    ];

    //var_dump($elements);
    $elements['#attached']['library'][] = 'image_lightgallery_formatter/image_lightgallery';
    return $elements;
  }

  protected function modifyChild($child) {

    $imgUrl = $child['#__url'];

    // turn the image into an bootstrap responsive image
    // TODO enable other options
    $attributes = [
      'class' => ['img-responsive']
    ];
    $child['#item_attributes'] = array_merge_recursive($child['#item_attributes'], $attributes);

    $newchild = [
      '#type' => 'inline_template',
      '#template' => sprintf(
        '<figure class="lg-thumbnail" data-src="%s">
                    <a href="#">
                        {{child}}
                        <div class="lg-thumbnail-zoom">
				                    <i class="fa fa-2x fa-search-plus" aria-hidden="true"></i>
                        </div>
                    </a>
                </figure>', $imgUrl),
      '#context' => [
        'child' => $child,
      ]
    ];
    //var_dump($child);
    return $newchild;
  }

}

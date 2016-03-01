<?php

namespace Brunops\Select24EntityBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Data transformer for multiple mode (i.e., multiple = true)
 *
 * Class EntitiesToPropertyTransformer
 * @package Brunops\Select24EntityBundle\Form\DataTransformer
 */
class EntitiesToPropertyTransformer implements DataTransformerInterface {

  /** @var EntityManager */
  protected $em;

  /** @var  string */
  protected $className;

  /** @var  string */
  protected $textProperty;

  /**
   * @param EntityManager $em
   * @param string $class
   * @param string|null $textProperty
   */
  public function __construct(EntityManager $em, $class, $textProperty = null) {
    $this->em = $em;
    $this->className = $class;
    $this->textProperty = $textProperty;
  }

  /**
   * Transform initial entities to array
   *
   * @param mixed $entities
   * @return array
   */
  public function transform($entities) {
    if (is_null($entities) || count($entities) === 0) {
      return array();
    }

    $data = array();

    $accessor = PropertyAccess::createPropertyAccessor();

    foreach ($entities as $entity) {
      $text = is_null($this->textProperty) ? (string) $entity : $accessor->getValue($entity, $this->textProperty);

      $data[$accessor->getValue($entity, 'id')] = $text;
    }

    return $data;
  }

  /**
   * Transform array to a collection of entities
   *
   * @param array $values
   * @return ArrayCollection
   */
  public function reverseTransform($values) {
    // $values is empty or not an array, we return an empty ArrayCollection
    if (!is_array($values) || count($values) === 0) {
      return new ArrayCollection();
    }

    // Retrieve all entities matching the IDs (in $values)
    $entities = new ArrayCollection($this->em->createQueryBuilder()
                    ->select('entity')
                    ->from($this->className, 'entity')
                    ->where('entity.id IN (:ids)')
                    ->setParameter('ids', $values)
                    ->getQuery()->getResult());

    // Retrieve all the values that are new.
    $newEntityValues = new ArrayCollection();
    $needle = 'new_';
    foreach ($values as $value) {
      if (strpos($value, $needle) === 0) {
        $newEntityValues->add(substr($value, strlen($needle)));
      }
    }

    // Add new Entities to array collection
    if ($newEntityValues->count() != 0) {
      $entities->set('toCreate', $newEntityValues);
    }

    return $entities;
  }

}

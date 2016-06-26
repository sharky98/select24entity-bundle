<?php

namespace Brunops\Select24EntityBundle\Form\DataTransformer;

use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Data transformer for multiple mode (i.e., multiple = true)
 *
 * Class EntitiesToPropertyTransformer
 * @package Brunops\Select24EntityBundle\Form\DataTransformer
 */
class EntitiesToPropertyTransformer implements DataTransformerInterface {

  /** @var EntityManagerInterface */
  protected $em;

  /** @var  string */
  protected $className;

  /** @var  string */
  protected $textProperty;

  /** @var  string */
  protected $primaryKey;

  /**
   * @param EntityManagerInterface $em
   * @param string $class
   * @param string|null $textProperty
   * @param string $primaryKey
   */
  public function __construct(EntityManagerInterface $em, $class, $textProperty = null, $primaryKey = 'id') {
    $this->em = $em;
    $this->className = $class;
    $this->textProperty = $textProperty;
    $this->primaryKey = $primaryKey;
  }

  /**
   * Transform initial entities to array
   *
   * @param mixed $entities
   * @return array
   */
  public function transform($entities) {
    if (empty($entities)) {
      return array();
    }

    $data = array();

    $accessor = PropertyAccess::createPropertyAccessor();

    foreach ($entities as $entity) {
      $text = is_null($this->textProperty) ? (string) $entity : $accessor->getValue($entity, $this->textProperty);
      $data[$accessor->getValue($entity, $this->primaryKey)] = $text;
    }

    return $data;
  }

  /**
   * Transform array to a collection of entities
   *
   * @param array $values
   * @return array
   */
  public function reverseTransform($values) {
    if (!is_array($values) || empty($values)) {
      return array();
    }

    try {
      // get multiple entities with one query
      $entities = new ArrayCollection(
              $this->em->createQueryBuilder()
                      ->select('entity')
                      ->from($this->className, 'entity')
                      ->where('entity.' . $this->primaryKey . ' IN (:ids)')
                      ->setParameter('ids', $values)
                      ->getQuery()->getResult()
      );

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
    } catch (DriverException $ex) {
      // this will happen if the form submits invalid data
      throw new TransformationFailedException('One or more id values are invalid');
    }

    return $entities;
  }

}

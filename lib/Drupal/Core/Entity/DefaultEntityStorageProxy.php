<?php

namespace Drupal\Core\Entity;

/**
 * Proxy towards the Drupal 7 \DrupalEntityControllerInterface
 */
class DefaultEntityStorageProxy implements EntityStorageInterface
{
    /**
     * @var \DrupalEntityControllerInterface
     */
    private $controller;

    /**
     * @var string
     */
    private $entityType;

    /**
     * Default constructor
     *
     * @param string $entityType
     */
    public function __construct($entityType)
    {
        $this->entityType = $entityType;
    }

    /**
     * Get Drupal 7 entity controller
     *
     * @return \DrupalEntityControllerInterface
     */
    final protected function getController()
    {
        if (!$this->controller) {
            $this->controller = entity_get_controller($this->entityType);

            if (!$this->controller) {
                throw new \InvalidArgumentException(sprintf("%s: entity type does not exist", $this->entityType));
            }
        }

        return $this->controller;
    }

    /**
     * {@inheritdoc}
     */
    public function resetCache(array $ids = NULL)
    {
        $this->getController()->resetCache($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function loadMultiple(array $ids = NULL)
    {
        return $this->getController()->load($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        $entities = $this->getController()->load([$id]);

        if ($entities) {
            return reset($entities);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadUnchanged($id)
    {
        $this->resetCache([$id]);

        return $this->load($id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByProperties(array $values = array())
    {
        return $this->getController()->load(null, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeId()
    {
        return $this->entityType;
    }

    /**
     * {@inheritdoc}
     */
    public function loadRevision($revision_id)
    {
        throw new \Exception("Not implemented yet");
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRevision($revision_id)
    {
        throw new \Exception("Not implemented yet");
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $values = array())
    {
        throw new \Exception("Not implemented yet");
    }

    /**
     * {@inheritdoc}
     */
    public function delete(array $entities)
    {
        throw new \Exception("Not implemented yet");
    }

    /**
     * {@inheritdoc}
     */
    public function save(EntityInterface $entity)
    {
        throw new \Exception("Not implemented yet");
    }
}

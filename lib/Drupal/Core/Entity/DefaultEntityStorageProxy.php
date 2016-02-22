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
     * @param \DrupalEntityControllerInterface $controller
     */
    public function __construct(\DrupalEntityControllerInterface $controller, $entityType)
    {
        $this->controller = $controller;
        $this->entityType = $entityType;
    }

    /**
     * Get Drupal 7 entity controller
     *
     * @return \DrupalEntityControllerInterface
     */
    protected function getController()
    {
        return $this->controller;
    }

    /**
     * {@inheritdoc}
     */
    public function resetCache(array $ids = NULL)
    {
        $this->getController()->resetCache();
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

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
    public function loadMultiple($ids = FALSE)
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
     * Attempt to use a callback, if any exists
     *
     * When dealing with Drupal 7 core or contrib entities, most modules will
     * actually implement "ENTITYTYPE_(delete|save)[_multiple]" callbacks,
     * let's attempt to use them transparently if possible.
     *
     * @param string $op
     * @param string $isMultiple
     * @param object|object[] $input
     *
     * @return void|mixed
     */
    protected function attemptCallbackUse($op, $isMultiple, $input)
    {
        if ($isMultiple) {

            if (!is_array($input)) {
                throw new \InvalidArgumentException("Cannot proceed with multiple without an array as parameter");
            }

            $function = $this->entityType . '_' . $op . '_multiple';
        } else {
            $function = $this->entityType . '_' . $op;
        }

        if (function_exists($function)) {
            return $function($input);
        }

        if (!$isMultiple) {
            return $this->attemptCallbackUse($op, true, [$input]);
        }
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
        return (object)$values;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(array $entities)
    {
        return $this->attemptCallbackUse('delete', true, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function save($entity)
    {
        return $this->attemptCallbackUse('save', false, $entity);
    }
}

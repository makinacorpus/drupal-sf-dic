<?php

namespace MakinaCorpus\Drupal\Sf\EventDispatcher;

use Drupal\Core\Entity\EntityInterface;

use Symfony\Component\EventDispatcher\GenericEvent;

class EntityEvent extends GenericEvent
{
    use EntityEventTrait;

    const EVENT_DELETE      = 'entity:delete';
    const EVENT_INSERT      = 'entity:insert';
    const EVENT_PREINSERT   = 'entity:preinsert';
    const EVENT_PREPARE     = 'entity:prepare';
    const EVENT_PREUPDATE   = 'entity:preupdate';
    const EVENT_PRESAVE     = 'entity:presave';
    const EVENT_SAVE        = 'entity:save';
    const EVENT_UPDATE      = 'entity:update';
    const EVENT_VIEW        = 'entity:view';

    /**
     * Constructor
     *
     * @param string $eventName
     * @param string $entityType
     * @param EntityInterface $entity
     * @param int $userId
     * @param array $arguments
     */
    public function __construct($eventName, $entityType, EntityInterface $entity, $userId = null, array $arguments = [])
    {
        $this->setEventName($eventName);
        $this->setEntityType($entityType);
        $this->setUserId($userId);

        list($id, , $bundle) = entity_extract_ids($entityType, $entity);

        // Keeping the 'uid' in arguments allows compatibility with the
        // makinacorpus/apubsub API, using subject too
        parent::__construct($entity, $arguments + ['uid' => $userId, 'id' => $id, 'bundle' => $bundle]);
    }

    /**
     * Get original entity
     *
     * @return EntityInterface
     */
    public function getOriginalEntity()
    {
        $entity = $this->getEntity();

        if ($entity->isNew()) {
            throw new \LogicException(sprintf('entity is new for event %s', $this->getEventName()));
        }
        if (isset($entity->original)) {
            return $entity->original;
        }

        // No entity, attempt original entity load
        $original = entity_load_unchanged($this->getEntityType(), $this->getEntityId());
        if ($original) {
            return $original;
        }

        throw new \LogicException(sprintf('there is no original entity for event %s', $this->getEventName()));
    }

    /**
     * Get entity bundle
     *
     * @return scalar
     */
    final public function getEntityBundle()
    {
        return $this->getArgument('bundle');
    }

    /**
     * Get entity id
     *
     * @return scalar
     */
    final public function getEntityId()
    {
        return $this->getArgument('id');
    }

    /**
     * Get the node.
     *
     * @return EntityInterface
     */
    final public function getEntity()
    {
        return $this->subject;
    }
}

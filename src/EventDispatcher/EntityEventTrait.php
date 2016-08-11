<?php

namespace MakinaCorpus\Drupal\Sf\EventDispatcher;

trait EntityEventTrait
{
    private $eventName;
    private $entityType;
    private $userId;

    /**
     * Get the event name
     *
     * @return string
     */
    final public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * Set the event name
     *
     * @param string $eventName
     */
    final protected function setEventName($eventName)
    {
        $this->eventName = $eventName;
    }

    /**
     * Get entity type
     *
     * @return string
     */
    final public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Set entity type
     *
     * @param string $entityType
     */
    final protected function setEntityType($entityType)
    {
        $this->entityType = $entityType;
    }

    /**
     * Get the user identifier which triggered the event
     *
     * @return int
     */
    final public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the user identifier which triggered the event
     *
     * @param int $userId
     */
    final protected function setUserId($userId)
    {
        $this->userId = $userId;
    }
}

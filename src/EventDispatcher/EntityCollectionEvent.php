<?php

namespace MakinaCorpus\Drupal\Sf\EventDispatcher;

use Drupal\Core\Entity\EntityInterface;

use Symfony\Component\EventDispatcher\GenericEvent;

class EntityCollectionEvent extends GenericEvent
{
    use EntityEventTrait;

    const EVENT_LOAD        = 'entity:load';
    const EVENT_PREPAREVIEW = 'entity:prepareview';

    private $idList;
    private $bundleList;
    private $bundleMap;

    /**
     * Constructor
     *
     * @param string $eventName
     * @param string $entityType
     * @param EntityInterface[] $entities
     * @param int $userId
     * @param array $arguments
     */
    public function __construct($eventName, $entityType, array $entities, $userId = null, array $arguments = [])
    {
        $this->setEventName($eventName);
        $this->setEntityType($entityType);
        $this->setUserId($userId);

        // Keeping the 'uid' in arguments allows compatibility with the
        // makinacorpus/apubsub API, using subject too
        parent::__construct($entities, $arguments + ['uid' => $userId]);
    }

    /**
     * Prepare internal bundle and id list cache
     */
    private function buildListCache()
    {
        if (null !== $this->idList) {
            return;
        }

        foreach ($this->subject as $entity) {
            list($id,, $bundle) = entity_extract_ids($this->getEntityType(), $entity);

            $this->idList[] = $id;
            $this->bundleList[$id] = $bundle;
            $this->bundleList = $bundle;

            $this->bundleList = array_unique($this->bundleList);
        }
    }

    /**
     * Get an array of all entity identifiers
     *
     * @return scalar[]
     */
    final public function getEntityIdList()
    {
        $this->buildListCache();

        return $this->idList;
    }

    /**
     * Get an array of all bundles
     *
     * @return scalar[]
     */
    final public function getEntityBundleList()
    {
        $this->buildListCache();

        return $this->bundleList;
    }

    /**
     * Get an array of bundles, keyed by entity identifiers
     *
     * @return string[]
     */
    final public function getEntityBundleMap()
    {
        $this->buildListCache();

        return $this->bundleMap;
    }

    /**
     * Get the nodes
     *
     * @return EntityInterface[]
     */
    final public function getEntities()
    {
        return $this->subject;
    }
}

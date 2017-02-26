<?php

namespace MakinaCorpus\Drupal\Sf\DataCollector;

use Drupal\Core\Entity\EntityManager;

/**
 * Entity storage are loaded using the entity manger, we have to attach
 * on it to decorate them using the entity storage proxies.
 */
class EntityManagerProxy extends EntityManager
{
    private $nested;
    private $instances = [];

    /**
     * Default constructor
     *
     * @param EntityManager $nested
     */
    public function __construct(EntityManager $nested)
    {
        $this->nested = $nested;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage($entityType)
    {
        if (isset($this->instances[$entityType])) {
            return $this->instances[$entityType];
        }

        return $this->instances[$entityType] = new EntityStorageProxy($this->nested->getStorage($entityType));
    }
}

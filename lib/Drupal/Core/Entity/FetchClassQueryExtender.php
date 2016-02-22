<?php

namespace Drupal\Core\Entity;

/**
 * API compatible yet incomplete implementation of the Drupal 8 equivalent.
 */
class FetchClassQueryExtender extends \SelectQueryExtender
{
    /**
     * @var string
     */
    private $classname;

    /**
     * Set object class
     *
     * @param string $classname
     *
     * @return FetchClassQueryExtender
     */
    public function setObjectClass($classname)
    {
        if (!class_exists($classname)) {
            watchdog(__CLASS__, "@class does not exist", ['@class' => $classname], WATCHDOG_ERROR);
        } else {
            $this->classname = $classname;
        }

        return $this;
    }

    /**
     * Override the execute method.
     *
     * Before we run the query, we need to add pager-based range() instructions
     * to it.
     */
    public function execute()
    {
        if (!$this->classname) {
            return parent::execute();
        }

        if (!$this->preExecute($this)) {
            return NULL;
        }

        $statement = $this->query->execute();
        $statement->setFetchMode(\PDO::FETCH_CLASS, $this->classname);

        return $statement;
    }
}

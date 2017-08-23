Using Symfony event sucriber for Drupal events
==============================================

The ``sf_entity_event`` submodule essentially provides Symfony events related
to Drupal event hooks.

Event Classes list
-------------------

``MakinaCorpus\Drupal\Sf\EventDispatcher\EntityCollectionEvent``:
  - ``EVENT_LOAD``: Fired when multiple entities are loaded (``hook_entity_load``)
  - ``EVENT_PREPAREVIEW``: Fired when multiple entities are prepared for view (``hook_entity_prepare_view``)
``MakinaCorpus\Drupal\Sf\EventDispatcher\EntityEvent``:
  - ``EVENT_DELETE``: Fired when a single entity is deleted (``hook_entity_delete``)
  - ``EVENT_INSERT``: Fired when a single entity is inserted (``hook_entity_insert``)
  - ``EVENT_PREINSERT``: Fired when a single entity is about to be inserted (``hook_entity_presave``)
  - ``EVENT_PREPARE``: Fired when a single entity is prepared (``hook_entity_prepare``)
  - ``EVENT_PREUPDATE``: Fired when a single entity is is about to be updated (``hook_entity_presave``)
  - ``EVENT_PRESAVE``: Fired when a single entity is about to be saved (``hook_entity_presave``)
  - ``EVENT_SAVE``: Fired when a single entity is saved (``hook_entity_save``)
  - ``EVENT_UPDATE``: Fired when a single entity is updated (``hook_entity_update``)
  - ``EVENT_VIEW``: Fired when a single entity is viewed (``hook_entity_view``)
``MakinaCorpus\Drupal\Sf\EventDispatcher\NodeAccessEvent``:
  - ``EVENT_NODE_ACCESS``: Node access is checked (``hook_node_access``)
``MakinaCorpus\Drupal\Sf\EventDispatcher\NodeAccessGrantEvent``:
  - ``EVENT_NODE_ACCESS_GRANT``: Node grants are collected for user (``hook_node_grants``)
``MakinaCorpus\Drupal\Sf\EventDispatcher\NodeAccessRecordEvent``:
  - ``EVENT_NODE_ACCESS_RECORD``: Node access record are being collected (``hook_node_access_records``)
``MakinaCorpus\Drupal\Sf\EventDispatcher\NodeCollectionEvent``:
  - ``EVENT_LOAD``: Fired when multiple nodes are loaded (``hook_node_load``)
``MakinaCorpus\Drupal\Sf\EventDispatcher\NodeEvent``:
  - ``EVENT_DELETE``: Fired when a single node is deleted (``hook_node_delete``)
  - ``EVENT_INSERT``: Fired when a single node is inserted (``hook_node_insert``)
  - ``EVENT_PREINSERT``: Fired when a single node is about to be insertd (``hook_node_presave``)
  - ``EVENT_PREPARE``: Fired when a single node is prepared (``hook_node_prepare``)
  - ``EVENT_PREUPDATE``: Fired when a single node is about to be updated (``hook_node_presave``)
  - ``EVENT_PRESAVE``: Fired when a single node is about to be saved (``hook_node_presave``)
  - ``EVENT_SAVE``: Fired when a single node is saved (``hook_node_save``)
  - ``EVENT_UPDATE``: Fired when a single node is updated (``hook_node_update``)
  - ``EVENT_VIEW``: Fired when a single node is viewed (``hook_node_view``)


Example
-------------------

**your_module.services.yml**:

.. code-block:: yaml

    your_module.node_subscriber:
      public: true
      class: YourPackage\EventSubscriber\NodeEventSubscriber
      arguments: ["@database"]
      tags: [{ name: event_subscriber }]


**src/EventSubscriber/NodeEventSubscriber.php** :

.. code-block:: php

    <?php

    namespace YourPackage\EventSubscriber;

    use MakinaCorpus\Drupal\Sf\EventSubscriber\NodeEvent;

    class NodeEventSubscriber implements EventSubscriberInterface
    {
        /**
         * @var \DatabaseConnection
         */
        private $db;


        /**
         * {@inheritdoc}
         */
        static public function getSubscribedEvents()
        {
            return [
                NodeEvent::EVENT_SAVE => [
                    ['onSave', 0]
                ],
            ];
        }

        /**
         * Constructor
         *
         * @param \DatabaseConnection $db
         */
        public function __construct(\DatabaseConnection $db)
        {
            $this->db = $db;
        }

        public function onSave(NodeEvent $event)
        {
            $node = $event->getNode();
            // Do something...
            $this->db->select(/* ... */);
        }
    }

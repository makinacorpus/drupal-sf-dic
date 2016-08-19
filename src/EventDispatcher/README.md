# Event driven Drupal node access API

This API leverage Drupal node_access records/grants API with an object oriented
API with a Domain Specific Language on its own, basically, this allows modules
to interact and override each other without the need of having one hook then
an alter hook.

## Node access records

Basic ```MakinaCorpus\Drupal\Sf\EventDispatcher\NodeAccessRecordEvent``` is
sufficient by itself, it works by building a right matrix that does not matches
the core structure, but is a more compact and more efficient structure, we
will see later why this is important.

## User grants

The ```MakinaCorpus\Drupal\Sf\EventDispatcher\NodeAccessGrantEvent``` allows
any module to add user grants, one thing you should know is that you must always
match both of the node records and user grants, or implement none, this will be
important later.

## Node access

On normal runtime, the core ```hook_node_access()``` allows you to do efficient
shortcuts depending on your business API, which will give you the best
performances when implemented properly, but as you may know, it's often hard
to have both specific generic (but slower) implementations. This allows you
to combine both, using the ```MakinaCorpus\Drupal\Sf\EventDispatcher\NodeAccessEvent```:

 *  use the ``NodeAccessRecordEvent`` event to set grants for node, but please
    let it be the fastest possible (no SQL queries, no cache queries) ;

 *  use the ``NodeAccessGrantEvent`` to build user grants, they will be then
    cached by this very module, but make it the fastest possible because
    this will be run on each HTTP hit ;

 *  use the ``NodeAccessEvent`` to **only implement the shorcuts** and make it
    listen with a priority inferior to ``-2048`` ; this important.

What will happen next is that if you implemented correctly the node records and
the matching user grants, an automatic listener will be run with a very low
priority (ie. ``-2048``) in order to ensure that it'll run *after* your own
module shortcut. This implementation will run the node record collect event,
the user grant collect event, then intersect them.

This might be a very slow implementation, so please implement correctly as
many shortcuts as you can in order for this implementation to avoid running
most of the time.

## Some important notes

### Additional shortcut

It also important to understand that any ``DENY`` call on ``NodeAccessEvent``
instance will actually stop propagation of the event (in opposition to Drupal
hooks), thus will ensure that any other useless code will never run.

### Where does it integrates with Drupal

 *  the ``hook_node_access()`` will run the ``NodeAccessEvent`` for everyone
    and will work as described upper ;

 *  the ``hook_node_access_grants()`` will not run any events, since we do
    handle user grants ourselves, this important that it remains this way ;

 *  the ``hook_node_access_records()`` will run the ``NodeAccessRecordEvent``
    event as expected in order to save Drupal grants, as expected.


# MarelloNotificationMessageBundle

MarelloNotificationMessageBundle provides tools and solutions for new activity - notification messages.

## Usage

```
use Marello\Bundle\NotificationMessageBundle\Event\CreateNotificationMessageEvent;
use Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory;
...

...
$context = NotificationMessageContextFactory::createSuccess(                   // There are 4 main notification message types: error, warning, info, success
    NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_WEBHOOK,   // You must specify the source, i.e. area of a message
    'marello.notificationmessage.webhook.cant_update.title',                   // Title of the notification message that would be displayed in datagrid/breadcrumbs/sidebar widget. Can be simple text, or msgid from 'notificationMessage.en.yml' catalogs
    'marello.notificationmessage.webhook.cant_update.message',                 // Message that describes the root of the notification message. Can be simple text, or msgid from 'notificationMessage.en.yml' catalogs
    'marello.notificationmessage.webhook.cant_update.solution',                // Optional. Solution = description with a possible solution how to resolve the message, or with a useful information. Can be simple text, or msgid from 'notificationMessage.en.yml' catalogs
    $entity                                                                    // Optional. Related entity that would be used as a context for the activity
    $operation
    $step,
    $externalId,
    $log,
    $flush                                                                     // Trigger that determinate do we need to call EntitiManager::flush() method or not. Can be usefull to use $flush=false inside doctrine events
);

$this->eventDispatcher->dispatch(                                            // Trigger CreateNotificationMessageEvent to create the entity
    new CreateNotificationMessageEvent($context),
    CreateNotificationMessageEvent::NAME
);
```

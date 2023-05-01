# MarelloNotificationMessageBundle

MarelloNotificationMessageBundle provides tools and solutions for new activity - notification messages.

## Usage

```
use Marello\Bundle\NotificationMessageBundle\Event\CreateNotificationMessageEvent;
use Marello\Bundle\NotificationMessageBundle\Factory\NotificationMessageContextFactory;
...

...
$context = NotificationMessageContextFactory::createSuccess(                   // There are 4 main notification message types: error, warning, info, success
    NotificationMessageSourceInterface::NOTIFICATION_MESSAGE_SOURCE_WEBHOOK,     // You must specify the source, i.e. area of a message
    'marello.notificationmessage.webhook.cant_update.message',                 // Message = title of the notification message. Can be simple text, or msgid from 'notificationMessage.en.yml' catalogs
    'marello.notificationmessage.webhook.cant_update.solution',                // Optional. Solution = description with a possible solution how to resolve the message, or with a useful information. Can be simple text, or msgid from 'notificationMessage.en.yml' catalogs
    $entity                                                                  // Optional. Related entity that would be used as a context for the activity
);

$this->eventDispatcher->dispatch(                                            // Trigger CreateNotificationMessageEvent to create the entity
    new CreateNotificationMessageEvent($context),
    CreateNotificationMessageEvent::NAME
);
```

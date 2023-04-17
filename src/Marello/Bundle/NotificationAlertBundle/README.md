# MarelloNotificationAlertBundle

MarelloNotificationAlertBundle provides tools and solutions for new activity - notification alerts.

## Usage

```
use Marello\Bundle\NotificationAlertBundle\Event\CreateNotificationAlertEvent;
use Marello\Bundle\NotificationAlertBundle\Factory\NotificationAlertContextFactory;
...

...
$context = NotificationAlertContextFactory::createSuccess(                   // There are 4 main alert types: error, warning, info, success
    NotificationAlertSourceInterface::NOTIFICATION_ALERT_SOURCE_WEBHOOK,     // You must specify the source, i.e. area of an alert
    'marello.notificationalert.webhook.cant_update.message',                 // Message = title of the notification alert. Can be simple text, or msgid from 'notificationAlert.en.yml' catalogs
    'marello.notificationalert.webhook.cant_update.solution',                // Optional. Solution = description with a possible solution how to resolve the alert, or with a useful information. Can be simple text, or msgid from 'notificationAlert.en.yml' catalogs
    $entity                                                                  // Optional. Related entity that would be used as a context for the activity
);

$this->eventDispatcher->dispatch(                                            // Trigger CreateNotificationAlertEvent to create the entity
    new CreateNotificationAlertEvent($context),
    CreateNotificationAlertEvent::NAME
);
```

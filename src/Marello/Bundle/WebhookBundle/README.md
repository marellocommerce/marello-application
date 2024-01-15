# MarelloWebhookBundle

MarelloWebhookBundle provides tools to define webhooks and use it to send webhook data.

## How to use it

### Create own webhook

First need to create Webhook event type. Create model implements `Marello\Bundle\WebhookBundle\Event\WebhookEventInterface` and register it as a service.
````
namespace CompanyAcmeBundle\Model;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\WebhookBundle\Event\AbstractWebhookEvent;

class TestWebhook extends AbstractWebhookEvent
{
    public static function getName(): string
    {
        return 'acme_company.test_webhook';
    }

    public static function getLabel(): string
    {
        return 'Test webhook';
    }

    protected function getContextData(): array
    {
        return [
            ...
        ];
    }
````

It's possible to define the service with autoconfiguration to automatically set `marello_webhook.event` tag, or do it manually.
````
services:
    _defaults:
        autoconfigure: true

    CompanyAcmeBundle\Model\TestWebhook: ~
````
or:
````
services:
    CompanyAcmeBundle\Model\TestWebhook:
        tags:
            - { name: marello_webhook.event }
````

Then create a webhook through the CRUD (Application Menu -> System -> Webhooks).

And there is need to create an integration with Webhook type to have possibility to send webhooks.

### Sample implementation from receiver's side

This example is an implementation on Oro based application, simply create a controller that will receive the POST requests.

Resources/oro/app.yml
````
security:
    firewalls:
        acme_webhook:
            pattern: '^%web_backend_prefix%/acme/webhook/*'
            security: false
            anonymous: true
````


Resources/oro/routing.yml
```
acme_route:
resource:     "@CompanyAcmeBundle/Controller"
type:         annotation
prefix:       /webhook
```

Controller.php
````
    /**
     * @Route("/notify", name="acme_route", options={"expose"=true}, methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function notifyAction(Request $request)
    {
        $logger = $this->getLogger();

        $logger->alert("CONTENT:" . $request->getContent());
        $logger->alert("CLIENT IP:" . $request->getClientIp());
        $logger->alert("HEADERS:");
        $logger->alert(print_r($request->headers->all('authorization'), true));

        return $this->createResponse();
    }
````


Sample results webhook notification receiver:
```
[2023-06-29 14:41:58] app.ALERT: CONTENT:{"inventory":1,"inventory_level_qty":95,"change_trigger":"manual","sku":"EQMBS03009-sla0-40","warehouse":"store_warehouse_de_berlin"} [] []
[2023-06-29 14:41:58] app.ALERT: CLIENT IP:xx.xx.xx.xx [] []
[2023-06-29 14:41:58] app.ALERT: HEADERS: [] []
[2023-06-29 14:41:58] app.ALERT: Array (     [0] => HTTP_X_MARELLO_SIGNATURE g7x9xBdbbUoap8rZJAaW3gR1MG95ZS9baanrZx+sN0o= )  [] []
```

### Signature validation
````
$webhookPayloadRaw = file_get_contents('php://input');
$checkingSignature = base64_encode(
        hash_hmac('sha256', $webhookPayloadRaw, 'your-provided-secret', true)
    );

$isSignatureValid = hash_equals($checkingSignature, $_SERVER['HTTP_X_MARELLO_SIGNATURE']);
````

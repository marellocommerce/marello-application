# Sample implimentation
This was an implementation on orocommerce, simply create a controller that will receive the POST requests.

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
     * @param string  $token
     * @param Request $request
     *
     * @return Response
     */
    public function notifyAction($token, Request $request)
    {
        $logger = $this->getLogger();

        //TODO: log
        $logger->alert("CONTENT:" . $request->getContent());
        $logger->alert("TOKEN:" . $token);
        $logger->alert("CLIENT IP:" . $request->getClientIp());
        $logger->alert("HEADERS:");
        $logger->alert(print_r($request->headers->all('authorization'), true));

        return $this->createResponse();
    }
````


Sample results webhook notification receiver:
```
[2023-06-29 14:41:58] app.ALERT: CONTENT:{"inventory":1,"inventory_level_qty":95,"change_trigger":"manual","sku":"EQMBS03009-sla0-40","warehouse":"store_warehouse_de_berlin"} [] []
[2023-06-29 14:41:58] app.ALERT: TOKEN:token [] []
[2023-06-29 14:41:58] app.ALERT: CLIENT IP:xx.xx.xx.xx [] []
[2023-06-29 14:41:58] app.ALERT: HEADERS: [] []
[2023-06-29 14:41:58] app.ALERT: Array (     [0] => HTTP_MARELLO_SIGNATURE 63d49fd367119b615d15ff20b34cedb85b192f841118f1011dc1bf12aa23a77e )  [] []
```


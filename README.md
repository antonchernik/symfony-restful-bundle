## How to install

```sh
composer require antonchernik/restful-bundle
```

Register the bundle:

```php
// config/bundles.php

return [
    ...
    RestfulBundle\RestfulBundle::class => ['all' => true],
];
```

Add configuration to services.yaml:
```yaml
parameters:
  restful_bundle.validation.message_map: !php/const App\Dictionary\ValidationMessages::MESSAGE_MAP
  #or use Symfony Standard RestfulBundle\Dictionary\ValidationMessages::MESSAGE_MAP
```
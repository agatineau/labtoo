CocoricoDisputeBundle
===========================

CocoricoDisputeBundle is a private cocorico bundle to dispute booking by asker.

# Installation

## Edit `composer.json` file:

```json
    ...
    "repositories": [
        {
          "type": "composer",
          "url": "https://packages.cocorico.io",
          "options": {
            "ssl": {
              "verify_peer": true,
              "allow_self_signed": true
            }
          }
        }
    ],
    ...
```

## Copy / paste auth.json.dist to auth.json and add Cocorico account in `auth.json` in "http-basic" part.

## Download the Bundle:

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

```bash
$ composer require cocorico/dispute-bundle
```

## Enable the Bundle:

Enable the bundle by adding the following lines in the `app/config/AppKernel.php` file of your project:

```php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Cocorico\DisputeBundle\CocoricoDisputeBundle(),
        );

        return $bundles;
    }
```

## Import the default parameters:

Import the default parameters by adding the following line in the `app/config/config.yml` file of your project:

```yaml
imports:
    ...
    - { resource: "@CocoricoDisputeBundle/Resources/config/parameters.yml"}
```

## Import the route:

Import the route by adding the following line in the `app/config/routing.yml` file of your project:

```yaml
...
cocorico_dispute:
    resource: "@CocoricoDisputeBundle/Resources/config/routing.yml"

```

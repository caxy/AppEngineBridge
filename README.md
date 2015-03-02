Google App Engine Bridge
========================

This package supports Symfony and related components on Google App Engine.

Features
--------

* Monolog handler specifically for App Engine's simple `syslog()` facility.
* Pimple service provider for running Silex on App Engine.
* Abstract kernel base class that sets cache and log directories for App Engine.
* This README.

Installation
------------

Add to `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/caxy/AppEngineBridge.git"
        }
    ],
    "require": {
        "caxy/appengine-bridge": "~1.0@dev"
    }
}
```

### Silex

Register the `AppEngineProvider` after your other providers have been registered.

```php
<?php
$app->register(new \Caxy\AppEngine\Bridge\Pimple\Provider\AppEngineProvider());
```

Make sure that the `php55` runtime is set in your `app.yaml` or else Silex will fail
because `tempnam()` support is only in `php55`.

### Symfony

Use the Google App Engine configuration file `app.yaml` to set environment variables.
Using the `php55` runtime is required. The `SYMFONY__APP_ENGINE__DEFAULT_BUCKET_NAME`
value becomes a container parameter and is used to set up cache and log directories. Here
is a work-in-progress example:

```yaml
application: SOMETHING
version: 1
runtime: php55
api_version: 1
threadsafe: true

handlers:
- url: /bundles
  static_dir: web/bundles
- url: .*
  script: web/app.php

skip_files:
- ^(.*/)?#.*#$
- ^(.*/)?.*~$
- ^(.*/)?.*\.py[co]$
- ^(.*/)?.*/RCS/.*$
- ^(.*/)?\..*$
- ^(.*/)?.*/Tests/.*$
- var/cache/*
- var/logs/*

env_variables:
  SYMFONY_ENV: prod
  SYMFONY_DEBUG: 0
  SYMFONY__APP_ENGINE__DEFAULT_BUCKET_NAME: 'SOMETHING.appspot.com'
```

Replace your front controller entirely so you can switch between environments using the
`app.yaml` environment variable. The example here is a combination of Symfony framework
Standard Edition's `app.php` and `app_dev.php`.

```php
<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

$loader = require_once __DIR__.'/../var/bootstrap.php.cache';
if ((bool) $_SERVER['SYMFONY_DEBUG']) {
    Debug::enable();
}

// Enable APC for autoloading to improve performance.
// You should change the ApcClassLoader first argument to a unique prefix
// in order to prevent cache key conflicts with other applications
// also using APC.
/*
$apcLoader = new ApcClassLoader(sha1(__FILE__), $loader);
$loader->unregister();
$apcLoader->register(true);
*/

require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

$kernel = new AppKernel($_SERVER['SYMFONY_ENV'], (bool) $_SERVER['SYMFONY_DEBUG']);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
```

Replace the Kernel that your `AppKernel` extends.

```diff
diff --git a/app/AppKernel.php b/app/AppKernel.php
index 7673684..0d03d5a 100644
--- a/app/AppKernel.php
+++ b/app/AppKernel.php
@@ -1,6 +1,6 @@
 <?php
 
-use Symfony\Component\HttpKernel\Kernel;
+use Caxy\AppEngine\Bridge\HttpKernel\Kernel;
 use Symfony\Component\Config\Loader\LoaderInterface;
 
 class AppKernel extends Kernel
```
 

Establish `memcache` session handler. In `app/config/services.yml`:

```yaml
services:
    session.memcache:
        class: Memcache

    session.handler.memcache:
        class:     Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler
        arguments: [ @session.memcache ]
```

The Symfony profiler can store data in Memcached too. The specific host and port of this
DSN are not important as GAE supplies its own PHP `Memcache` object.

```yaml
framework:
    profiler:
        dsn: 'memcache://localhost:11211'
```

Use the `syslog` Monolog handler. The `ident` value is unimportant but required for
Symfony to instantiate the handler:

```yaml
monolog:
    handlers:
        main:
            type:         fingers_crossed
            action_level: debug
            handler:      syslog
        syslog:
            type: syslog
            ident: whatever
```

Features to do
--------------

* Easily purge Symfony cache on GAE.
* Build the application without developer dependencies prior to updating.

Other Niceties
--------------

In your `composer.json` add a new `scripts` entry so that you can deploy easily from
composer.

```json
{
    "scripts": {
        "appengine-update": [
             "appcfg.py update . --oauth2"
        ]
    }
}
```

Now you can deploy with the command `composer appengine-update`. Additional commands can
be stacked together.

```json
{
    "scripts": {
        "appengine-update": [
             "composer dumpautoload -o",
             "appcfg.py update . --oauth2"
        ]
    }
}
```

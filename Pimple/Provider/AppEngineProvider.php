<?php

namespace Caxy\AppEngine\Bridge\Pimple\Provider;

use Caxy\AppEngine\Bridge\Twig\Environment;
use google\appengine\api\cloud_storage\CloudStorageTools;
use Caxy\AppEngine\Bridge\Monolog\Handler\SyslogHandler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Provider\MonologServiceProvider;

/**
 * Class AppEngineProvider
 */
class AppEngineProvider implements ServiceProviderInterface
{
    /**
     *
     */
    const GAE_APP_ID = 'Google App Engine';

    private $isAppEngine = false;

    /**
     *
     */
    public function __construct()
    {
        $this->isAppEngine = substr($_SERVER['SERVER_SOFTWARE'], 0, strlen(self::GAE_APP_ID)) === self::GAE_APP_ID;
    }

    /**
     * {@inheritdocs}
     */
    public function register(Container $pimple)
    {
        if (!$this->isAppEngine) {
            return;
        }

        $pimple['google.storage_bucket.default'] = CloudStorageTools::getDefaultGoogleStorageBucketName();

        $pimple['twig.environment_factory'] = $pimple->protect(function (Container $pimple) {
            $options = array('cache' => 'gs://'.$pimple['google.storage_bucket.default'].'/var/cache/twig');

            return new Environment($pimple['twig.loader'], array_merge($pimple['twig.options'], $options));
        });

        $pimple['monolog.handler'] = function (Container $pimple) {
            $level = MonologServiceProvider::translateLevel($pimple['monolog.level']);

            $handler = new SyslogHandler($level, $pimple['monolog.bubble']);

            return $handler;
        };
    }
}

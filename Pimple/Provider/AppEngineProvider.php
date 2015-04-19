<?php

namespace Caxy\AppEngine\Bridge\Pimple\Provider;

use google\appengine\api\cloud_storage\CloudStorageTools;
use Caxy\AppEngine\Bridge\Monolog\Handler\SyslogHandler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Provider\MonologServiceProvider;

/**
 * Class AppEngineProvider.
 */
class AppEngineProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdocs}.
     */
    public function register(Container $pimple)
    {
        $pimple['google.storage_bucket.default'] = CloudStorageTools::getDefaultGoogleStorageBucketName();

        $pimple['twig.options'] = array('cache' => 'gs://'.$pimple['google.storage_bucket.default'].'/var/cache/twig');

        $pimple['monolog.handler'] = function (Container $pimple) {
            $level = MonologServiceProvider::translateLevel($pimple['monolog.level']);

            $handler = new SyslogHandler(LOG_USER, $level, $pimple['monolog.bubble']);

            return $handler;
        };
    }
}

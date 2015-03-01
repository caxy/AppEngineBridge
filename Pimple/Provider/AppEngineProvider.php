<?php

namespace Caxy\AppEngine\Bridge\Pimple\Provider;

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
     * @var bool
     */
    private $isAppEngine = false;

    /**
     *
     */
    public function __construct()
    {
        $this->isAppEngine = strpos($_SERVER['SERVER_SOFTWARE'], 'Google App Engine') === 0;
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

        $pimple['twig.options'] = array('cache' => 'gs://'.$pimple['google.storage_bucket.default'].'/var/cache/twig');

        $pimple['monolog.handler'] = function (Container $pimple) {
            $level = MonologServiceProvider::translateLevel($pimple['monolog.level']);

            $handler = new SyslogHandler($level, $pimple['monolog.bubble']);

            return $handler;
        };
    }
}

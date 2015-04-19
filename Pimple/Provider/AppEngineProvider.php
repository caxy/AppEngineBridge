<?php

namespace Caxy\AppEngine\Bridge\Pimple\Provider;

use Caxy\AppEngine\Bridge\Monolog\Handler\SyslogHandler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Provider\MonologServiceProvider;
use Symfony\Component\HttpKernel\Profiler\MysqlProfilerStorage;

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
        $pimple['app_engine.default_database_dsn'] = 'mysql:unix_socket='.$pimple['database_unix_socket'].';dbname='.$pimple['database_name'];

        $pimple['monolog.handler'] = function (Container $pimple) {
            $level = MonologServiceProvider::translateLevel($pimple['monolog.level']);

            $handler = new SyslogHandler(LOG_USER, $level, $pimple['monolog.bubble']);

            return $handler;
        };

        $pimple['profiler.storage'] = function (Container $pimple) {
            return new MysqlProfilerStorage($pimple['app_engine.default_database_dsn'], $pimple['database_user'], isset($pimple['database_password']) ?: '');
        };
    }
}

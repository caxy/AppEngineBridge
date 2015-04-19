<?php

namespace Caxy\AppEngine\Bridge\Pimple\Provider;

use Caxy\AppEngine\Bridge\Monolog\Handler\SyslogHandler;
use Caxy\AppEngine\Bridge\Security\Authentication\AppEngineAuthenticationProvider;
use Caxy\AppEngine\Bridge\Security\Firewall\AppEngineAuthenticationListener;
use Caxy\AppEngine\Bridge\Security\User\AppEngineUserProvider;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Provider\MonologServiceProvider;
use Symfony\Component\HttpKernel\Profiler\MysqlProfilerStorage;

/**
 * Class AppEngineProvider.
 */
class AppEngineProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['app_engine.default_database_dsn'] = 'mysql:unix_socket='.$pimple['database_unix_socket'].';dbname='.$pimple['database_name'];

        $pimple['monolog.handler'] = function (Container $pimple) {
            $level = MonologServiceProvider::translateLevel($pimple['monolog.level']);

            $handler = new SyslogHandler(LOG_USER, $level, $pimple['monolog.bubble']);

            return $handler;
        };

        $pimple['profiler.storage'] = function (Container $pimple) {
            if (isset($pimple['database_password'])) {
                return new MysqlProfilerStorage($pimple['app_engine.default_database_dsn'], $pimple['database_user'], $pimple['database_password']);
            }

            return new MysqlProfilerStorage($pimple['app_engine.default_database_dsn'], $pimple['database_user']);
        };

        $pimple['app_engine.security.user_provider.default.user_roles'] = array('ROLE_USER');
        $pimple['app_engine.security.user_provider.default.admin_roles'] = array('ROLE_ADMIN');
        $pimple['app_engine.security.user_provider.default'] = function (Container $pimple) {
            return new AppEngineUserProvider($pimple['app_engine.security.user_provider.default.user_roles'], $pimple['app_engine.security.user_provider.default.admin_roles']);
        };

        $pimple['security.authentication_listener.factory.app_engine'] = $pimple->protect(function ($name, $options) use ($pimple) {
            // define the authentication provider object
            $pimple['security.authentication_provider.'.$name.'.app_engine'] = function () use ($name, $pimple) {
                return new AppEngineAuthenticationProvider($pimple['app_engine.security.user_provider.'. $name]);
            };

            // define the authentication listener object
            $pimple['security.authentication_listener.'.$name.'.app_engine'] = function () use ($pimple) {
                // use 'security' instead of 'security.token_storage' on Symfony <2.6
                return new AppEngineAuthenticationListener($pimple['security.token_storage'], $pimple['security.authentication_manager'], $pimple['logger'], $pimple['dispatcher']);
            };

            return array(
                // the authentication provider id
                'security.authentication_provider.'.$name.'.app_engine',
                // the authentication listener id
                'security.authentication_listener.'.$name.'.app_engine',
                // the entry point id
                null,
                // the position of the listener in the stack
                'pre_auth'
            );
        });
    }
}

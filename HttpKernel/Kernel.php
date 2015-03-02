<?php

namespace Caxy\AppEngine\Bridge\HttpKernel;

use google\appengine\api\cloud_storage\CloudStorageTools;

/**
 * Class Kernel
 * @package Caxy\AppEngine\Bridge\HttpKernel
 */
abstract class Kernel extends \Symfony\Component\HttpKernel\Kernel
{
    /**
     * @var string
     */
    private $defaultStorageBucketName;

    /**
     * {@inheritdoc}
     */
    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
        if (self::isAppEngine()) {
            // https://cloud.google.com/appengine/docs/php/#PHP_Disabled_functions
            libxml_disable_entity_loader(false);
            $this->defaultStorageBucketName = CloudStorageTools::getDefaultGoogleStorageBucketName();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        if (self::isAppEngine()) {
            return 'gs://'. $this->defaultStorageBucketName .'/var/cache/'. $this->environment;
        }
        return parent::getCacheDir();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        if (self::isAppEngine()) {
            return 'gs://'. $this->defaultStorageBucketName .'/var/logs';
        }
        return parent::getLogDir();
    }

    /**
     * Test if this kernel is running on Google App Engine.
     *
     * @return bool
     */
    protected static function isAppEngine()
    {
        return (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Google App Engine') !== false);
    }
}

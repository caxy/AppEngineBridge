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
        if ($this->environment === 'appengine') {
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
        if ($this->environment === 'appengine') {
            return 'gs://'. $this->defaultStorageBucketName .'/var/cache/'. $this->environment;
        }
        return parent::getCacheDir();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        if ($this->environment === 'appengine') {
            return 'gs://'. $this->defaultStorageBucketName .'/var/logs';
        }
        return parent::getLogDir();
    }
}

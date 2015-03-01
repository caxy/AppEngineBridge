<?php

namespace Caxy\AppEngine\Bridge\Twig;

/**
 * Class Environment
 */
class Environment extends \Twig_Environment
{
    protected function writeCacheFile($file, $content)
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                clearstatcache(false, $dir);
                if (!is_dir($dir)) {
                    throw new \RuntimeException(sprintf("Unable to create the cache directory (%s).", $dir));
                }
            }
        } elseif (!is_writable($dir)) {
            throw new \RuntimeException(sprintf("Unable to write in the cache directory (%s).", $dir));
        }

        if (false !== @file_put_contents($file, $content)) {
            // rename does not work on Win32 before 5.2.6
            @chmod($file, 0666 & ~umask());

            return;
        }

        throw new \RuntimeException(sprintf('Failed to write cache file "%s".', $file));
    }
}

<?php

namespace Caxy\AppEngine\Bridge\Monolog\Handler;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractSyslogHandler;

/**
 * Class SyslogHandler.
 */
class SyslogHandler extends AbstractSyslogHandler
{
    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        syslog($this->logLevels[$record['level']], (string) $record['formatted']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFormatter()
    {
        return new LineFormatter('%channel%: %message% %context% %extra%');
    }
}

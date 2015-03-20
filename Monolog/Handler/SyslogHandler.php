<?php

namespace Caxy\AppEngine\Bridge\Monolog\Handler;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Class SyslogHandler.
 */
class SyslogHandler extends AbstractProcessingHandler
{
    /**
     * Translates Monolog log levels to syslog log priorities.
     */
    protected $logLevels = array(
      Logger::DEBUG     => LOG_DEBUG,
      Logger::INFO      => LOG_INFO,
      Logger::NOTICE    => LOG_NOTICE,
      Logger::WARNING   => LOG_WARNING,
      Logger::ERROR     => LOG_ERR,
      Logger::CRITICAL  => LOG_CRIT,
      Logger::ALERT     => LOG_ALERT,
      Logger::EMERGENCY => LOG_EMERG,
    );

    /**
     * Writes the record down to the log of the implementing handler.
     *
     * @param array $record
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

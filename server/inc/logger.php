<?php
namespace JHM;

use Monolog\Handler\StreamHandler;
use Monolog\Hanlder\PHPConsoleHandler;
use Psr\Log\InvalidArgumentException;

class Logger implements LoggerInterface
{
    protected $loggerEngine;

    protected $config;

    protected $logfile;

    protected $levels = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    public function __construct(ConfigInterface $config)
    {

        $this->config = $config;
        $this->loggerEngine = new \Monolog\Logger('jhm');
        $logdir = $this->config->getStorage('logs');
        $this->logfile = $logdir . 'jhm-system.log';
        if (!is_writable($this->logfile)) {
            throw new JhmException('Log file not writeable. Path: ' . $this->logfile);
        }

        try {
            if (array_key_exists('jhm-debug', $_COOKIE)) {
                $this->logger->pushHanlder(new PHPConsoleHandler());
            }
            $this->loggerEngine->pushHandler(new StreamHandler($this->logfile));

        } catch (Exception $e) {
            throw new Jhm_Exception('Logger not ready ' . $e->getMessage());
        }
    }

    public function loggingTo()
    {
        return $this->logfile;
    }

    public function log($level, $message, $context = [])
    {
        $level = strtoupper($level);
        if (in_array($level, $this->levels) && !empty($message)) {
            try {
                $this->loggerEngine->log($level, $message, $context);
            } catch (\PSR\Log\InvalidArgumentException $e) {
                $this->loggerEngine->log('WARNING', 'Bad Log Format', ['level' => $level, 'message' => $message]);
            } catch (Exception $e) {
                $this->loggerEngine->log('WARNING', 'Logging Failure', ['level' => $level, 'message' => $message]);
            }
        } else {
            $this->loggerEngine->log('WARNING', 'Bad Log Format', ['level' => $level, 'message' => $message]);
        }
    }

}

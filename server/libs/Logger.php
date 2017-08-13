<?php
namespace JHM;

use Monolog\Handler\StreamHandler;
use Monolog\Hanlder\PHPConsoleHandler;
use Psr\Log\InvalidArgumentException;

class Logger extends FileStorage implements LoggerInterface
{

    protected $enabled = true;

    protected $loggerEngine;

    protected $logFileName = 'jhm-system.log';

    protected $config;

    protected $logfile = '';

    protected $levels = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->enabled = $this->config->get('flags.loggingEnabled');
        if ($this->enabled) {
            $this->logFileName = date("Y.M.d") . '--' . $this->logFileName;
            $this->loggerEngine = new \Monolog\Logger('jhm');
            $logdir = $this->config->getStorage('logs');
            $this->logfile = $logdir . $this->logFileName;
            if (!$this->setupStorage($this->logfile)) {
                throw new JhmException('Log file not writeable. Path: ' . $this->logfile);
            }

            try {
                if (array_key_exists('jhm-debug', $_COOKIE)) {
                    $this->logger->pushHanlder(new PHPConsoleHandler());
                }
                $this->loggerEngine->pushHandler(new StreamHandler($this->logfile));
            } catch (Exception $e) {
                throw new JhmException('Logger not ready ' . $e->getMessage());
            }
        }
    }

    public function isEnabled()
    {
        return ($this->loggerEngine instanceof \Monolog\Logger);
    }

    public function loggingTo()
    {
        return $this->logfile;
    }

    public function log($level, $message, $context = [])
    {
        if ($this->enabled) {
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

}

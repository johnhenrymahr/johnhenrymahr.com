<?php
namespace JHM;

class Output
{

    protected $logger;

    protected $cacheInterface;

    protected $cacheReady;

    protected $buffer = '';

    public function __construct(CacheInterface $cache, LoggerInterface $logger, ConfigInterface $config)
    {
        $this->cacheInterface = $cache;
        $this->logger = $logger;
        $this->cacheReady = $cache->cacheReady();
        if (array_key_exists('jhm_disable_cache', $_COOKIE)) {
            $this->logger->log('DEBUG', 'jhm_disable_cache cookie detected; disabling cache engine.');
            $this->_clearCache();
        }
        if ($config->get('flags.cacheEnabled') === false) {
            $this->logger->log('DEBUG', 'disabling cache. Config flagged off.');
            $this->_clearCache();
        }
        if (array_key_exists('cache-control', $_GET)) {
            $this->_cachecontrol(filter_var($_GET['cache-control'], FILTER_SANITIZE_STRING));
        }
    }

    public function clear()
    {
        $this->buffer = '';
    }

    public function __invoke(callable $callable, $cacheKey = '', $options = [])
    {
        $output = '';
        if (!empty($cacheKey) && $this->cacheReady) {
            $this->logger->log('DEBUG', 'retrieving cache for key: ' . $cacheKey);
            $output = $this->cacheInterface->get($cacheKey);
        }
        if (empty($output)) {
            $output = call_user_func($callable, $options);
            if (empty($output)) {
                $this->logger->log('WARNING', 'Output: no output produced from callable',
                    array('callable' => $callable, 'options' => $options));
            }
            if (!empty($output) && !empty($cacheKey) && $this->cacheReady) {
                $this->logger->log('DEBUG', 'writing cache for key: ' . $cacheKey);
                $this->cacheInterface->set($cacheKey, $output);
                $this->cacheInterface->save();
            }
        }
        $this->buffer = $output;

        return $this;
    }

    public function toString()
    {
        return $this->buffer;
    }

    public function __tostring()
    {
        return $this->toString();
    }

    public function toJSON()
    {
        $output = @json_encode($this->buffer);
        if ($output === false) {
            $output = '';
        }
        return $output;
    }

    protected function _clearCache()
    {
        $this->cacheReady = false;
        $this->cacheInterface->clear();
    }

    private function _cachecontrol($cmd)
    {
        if (is_string($cmd) && !empty($cmd)) {
            $cmd = strtoupper($cmd);
            $this->logger->log('DEBUG', 'cache-control command: ' . $cmd);
            switch ($cmd) {
                case 'CLEAR':
                case 'PURGE':
                    $this->cacheInterface->clear();
                    break;
            }
        }
    }
}

<?php
namespace JHM;

class Output
{
    protected $cacheInterface;

    protected $cacheReady;

    protected $buffer = '';

    public function __construct(CacheInterface $cache)
    {
        $this->cacheInterface = $cache;
        $this->cacheReady = $cache->cacheReady();
        if (array_key_exists('jhm_disable_cache', $_COOKIE)) {
            $this->cacheReady = false;
            $this->cacheInterface->clear();
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
            $output = $this->cacheInterface->get($cacheKey);
        }
        if (empty($output)) {
            $output = call_user_func($callable, $options);
            if (!empty($output) && !empty($cacheKey) && $this->cacheReady) {
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
}

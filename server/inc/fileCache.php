<?php
namespace JHM;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\Exception\CacheException;

class FileCache extends FileStorage implements CacheInterface
{

    protected $config;

    protected $logger;

    protected $cacheEngine;

    protected $cacheItems = [];

    protected $storageReady = false;

    public function __construct(ConfigInterface $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $dir = $this->setupStorage($this->config->getStorage('filecache'), null);
        
        try {
            $this->cacheEngine = new FilesystemAdapter('', 86400, $dir);
        } catch (CacheException $e) {
            $logger->log('WARNING', 'Could not start cache engine. ' . $e->getMessage());
            $this->cacheEngine = false;
        }

    }

    public function cacheReady()
    {
        if ($this->cacheEngine instanceof FilesystemAdapter) {
            return true;
        } else {
            return false;
        }
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->cacheItems)) {
            return $this->cacheItems[$key]->get();
        }
        $item = $this->_getItem($key);
        try {
            if ($item->isHit()) {
                return $item->get();
            }
        } catch (CacheException $e) {
            return null;
        }
        return null;
    }

    protected function _getItem($key)
    {
        if ($this->cacheEngine) {
            if (array_key_exists($key, $this->cacheItems) && $this->cacheItems[$key] instanceof CacheItem) {
                $item = $this->cacheItems[$key];
            } else {
                try {
                    $item = $this->cacheEngine->getItem($key);
                    $this->cacheItems[$item->getKey()] = $item;
                } catch (CacheException $e) {
                    return null;
                }
            }
            return $item;
        }
        return null;
    }

    public function set($key, $value)
    {
        $item = $this->_getItem($key);
        if ($item) {
            try {
                $item->set($value);
            } catch (CacheException $e) {

            }
        }
    }

    public function save()
    {
        foreach ($this->cacheItems as $cache) {
            if ($cache instanceof CacheItem) {
                try {
                    $this->cacheEngine->save($cache);
                } catch (CacheException $e) {}
            }
        }
    }

    public function clear()
    {
        if ($this->cacheEngine) {
            $this->cacheEngine->clear();
        }
    }
}

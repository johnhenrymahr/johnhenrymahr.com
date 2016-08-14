<?php
namespace JHM;

class DataProvider implements DataProviderInterface
{

    protected $fileLoader;

    protected $logger;

    public function __construct(FileLoaderInterface $fileLoader, LoggerInterface $logger)
    {
        $this->fileLoader = $fileLoader;
        $this->logger = $logger;
    }
    public function getTemplateModel($templateId)
    {
        return [];
    }

    public function getBootstrapData()
    {
        return [];
    }
}

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
        $data = [];
        switch ($templateId) {
            case 'tech':
                $data['tech'] = $this->fileLoader->load('techlist.json', false, []);
                break;
        }
        return $data;
    }

    public function getBootstrapData()
    {
        return [];
    }
}

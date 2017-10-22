<?php
namespace JHM;

class DataProvider implements DataProviderInterface
{

    protected $csrfToken;

    protected $fileLoader;

    protected $logger;

    public function __construct(FileLoaderInterface $fileLoader, LoggerInterface $logger, CsrfTokenInterface $csrfToken)
    {
        $this->fileLoader = $fileLoader;
        $this->logger = $logger;
        $this->csrfToken = $csrfToken;
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
        $tokenField = $this->csrfToken->getField();
        return array(
            '_moduleData' => array(
                'contact' => array(
                    $tokenField => $this->csrfToken->generateToken('contact'),
                ),
                'cv' => array(
                    $tokenField => $this->csrfToken->generateToken('cv'),
                ),
            ),
        );
    }
}

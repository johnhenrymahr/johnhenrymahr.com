<?php
namespace JHM;

class Renderer implements RendererInterface
{

    protected $dustEngine;

    protected $logger;

    public function __construct(\Dust\Dust $dust, LoggerInterface $logger)
    {
        $this->dustEngine = $dust;
        $this->logger = $logger;
    }

    public function compileFile($path)
    {
        try {
            return $this->dustEngine->compileFile($path);
        } catch (\Dust\DustException $e) {
            $this->logger->log('ERROR', 'Could Not compile dust file', ["path" => $path, "exception" => $e->getMessage()]);
            return false;
        }
    }

    public function renderTemplate($template, $data)
    {
        try {
            return $this->dustEngine->renderTemplate($template, $data);
        } catch (\Dust\DustException $e) {
            $this->logger->log('ERROR', 'Could Not render dust template', ["template" => $template, "exception" => $e->getMessage()]);
            return '';
        }
    }

}

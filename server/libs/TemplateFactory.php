<?php
namespace JHM;

class TemplateFactory implements templateFactoryInterface
{

    protected $renderer;

    protected $config;

    protected $dataProvider;

    protected $queryPath;

    public function __construct(
        RendererInterface $renderer,
        ConfigInterface $config,
        DataProviderInterface $dataProvider) {
        $this->renderer = $renderer;
        $this->config = $config;
        $this->dataProvider = $dataProvider;
    }

    public function getTemplate($data = [])
    {
        if (array_key_exists('id', $data)) {
            $renderedContent = '';
            if (array_key_exists('template', $data)) {
                $templatePath = $this->config->resolvePath($data['template']);
                if ($templatePath) {
                    $template = $this->renderer->compileFile($templatePath);
                    if ($template) {
                        $modelData = $this->dataProvider->getTemplateModel($data['id']);
                        $renderedContent = $this->renderer->renderTemplate($template, $modelData);
                    }
                }
            }
            return $this->_templateFactory($data, $renderedContent);
        }

        return false;
    }
    protected function _templateFactory($data = [], $renderedContent = '')
    {
        return new Template($data, $renderedContent);
    }
}

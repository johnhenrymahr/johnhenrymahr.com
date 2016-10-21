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
        if (array_key_exists('template', $data) && array_key_exists('id', $data)) {
            $templatePath = $this->config->resolvePath($data['template']);
            $queryObj = null;
            if ($templatePath) {
                $renderedContent = '';
                $template = $this->renderer->compileFile($templatePath);
                if ($template) {
                    $modelData = $this->dataProvider->getTemplateModel($data['id']);
                    $renderedContent = $this->renderer->renderTemplate($template, $modelData);
                    if ($renderedContent) {
                        $queryObj = \QueryPath::with($renderedContent);
                    }
                }
            }
            if (is_null($queryObj)) {
                $queryObj = \QueryPath::with('');
            }
            return $this->_templateFactory($data, $queryObj);
        }

        return false;
    }

    protected function _templateFactory($data = [], \QueryPath\DOMQuery $renderedContent)
    {
        return new Template($data, $renderedContent);
    }
}

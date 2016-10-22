<?php
namespace JHM;

class TemplateFactory implements templateFactoryInterface
{

    use TemplateTraits;

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
            $bareElement = false; // does content need to be wrapped with extra container element
            if ($templatePath) {
                $renderedContent = '';
                $template = $this->renderer->compileFile($templatePath);
                if ($template) {
                    $modelData = $this->dataProvider->getTemplateModel($data['id']);
                    $renderedContent = $this->renderer->renderTemplate($template, $modelData);
                    if ($renderedContent) {
                        $bareElement = $this->_isBareElement($renderedContent);
                        $queryObj = $this->_getQueryObj($renderedContent, $bareElement);
                    }
                }
            }
            if (is_null($queryObj)) {
                $queryObj = \QueryPath::with('');
            }
            return $this->_templateFactory($data, $queryObj, $bareElement);
        }

        return false;
    }

    protected function _isBareElement($content)
    {
        return (strpos($content, '<') === false);
    }

    protected function _getQueryObj($content, $bareElement = false)
    {
        if ($bareElement) {
            $e = $this->BARE_ELEMENT_WRAPPER_ELEMENT;
            $c = $this->BARE_ELEMENT_WRAPPER_CLASS;
            return \QueryPath::with("<$e class=\"$c\">$content</$e>");
        } else {
            return \QueryPath::with($content);
        }
    }

    protected function _templateFactory($data = [], \QueryPath\DOMQuery $renderedContent)
    {
        return new Template($data, $renderedContent);
    }
}

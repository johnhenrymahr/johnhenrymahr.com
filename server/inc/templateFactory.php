<?php 
namespace JHM;
class templateFactory implements templateFactoryInterface {

	protected $renderer;

	protected $fileLoader;

	protected $dataProvider;

	public function __construct(
		JMH\RendererInterface $renderer, 
		JHM\FileLoaderInterface $fileLoader, 
		JHM\DataProviderInterface $dataProvider) {
		$this->renderer = $renderer;
		$this->fileLoader = $fileLoader;
		$this->dataProvider = $dataProvider;
	}

	public function getTemplate($data) {
		if (array_key_exists($data, 'template') && array_key_exists($data, 'id')) {
			$templateString = $this->fileLoader->getTemplate($data['template']);
			$renderedTemplate = '';
			if ($templateString) {
				$template = $this->renderer->compile($templateString);
				$data = $this->dataProvider->getTemplateModel($data['id']);
				$renderedTemplate = $this->renderer->render($template, $data);	
			}
			return new JHM\Template($data, $renderedTemplate);
		}
	}
}
?>
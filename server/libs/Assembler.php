<?php
namespace JHM;

class Assembler
{

    protected $manifest;

    protected $templateFactory;

    public function __construct(
        ManifestInterface $manifest,
        TemplateFactoryInterface $templateFactory
    ) {
        $this->manifest = $manifest;
        $this->templateFactory = $templateFactory;
    }

    /**
     * assemble
     * assemble markup from manifest data
     * @return string markup
     */
    public function assemble()
    {
        $mainTemplate = $this->templateFactory->getTemplate($this->manifest->getTopLevelData());
        if (!$mainTemplate) {
            throw new JhmException('Could not load main template');
        }
        foreach ($this->manifest->getSections() as $section) {
            if ($this->_shouldRender($section) === false) {
                continue;
            }
            $sectionTemplate = $this->templateFactory->getTemplate($section);
            if ($sectionTemplate) {
                if (array_key_exists('children', $section) && is_array($section['children'])) {
                    foreach ($this->manifest->getChildren($section) as $child) {
                        if ($this->_shouldRender($child) === false) {
                            continue;
                        }
                        $childTemplate = $this->templateFactory->getTemplate($child);
                        if ($childTemplate) {
                            $sectionTemplate->appendChild($childTemplate);
                        }
                    }
                }
                $mainTemplate->appendChild($sectionTemplate);
            }
        }
        return $mainTemplate->markup();
    }

    protected function _shouldRender(array $node)
    {
        if (array_key_exists('renderOnServer', $node) && $node['renderOnServer'] === false) {
            return false;
        }
        return true;
    }

}

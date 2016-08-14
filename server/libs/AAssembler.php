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

    public function assemble()
    {
        $markup = "";
        $mainTemplate = $this->templateFactory->getTemplate($this->manifest->getTopLevelData());
        if ($mainTemplate) {
            $markup .= $mainTemplate->open() . $mainTemplate->body();
        }
        foreach ($this->manifest->getSections() as $section) {
            $sectionTemplate = $this->templateFactory->getTemplate($section);
            if ($sectionTemplate) {
                $markup .= $sectionTemplate->open() . $sectionTemplate->body();
            }
            foreach ($this->manifest->getChildren($section) as $child) {
                $childTemplate = $this->templateFactory->getTemplate($child);
                if ($childTemplate) {
                    $markup .= $childTemplate->open() . $childTemplate->body() . $childTemplate->close();
                }
            }
            if ($sectionTemplate) {
                $markup .= $sectionTemplate->close();
            }
        }
        if ($mainTemplate) {
            $markup .= $mainTemplate->close();
        }

        return $markup;
    }

}

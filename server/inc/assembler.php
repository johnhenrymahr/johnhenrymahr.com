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
        $this->manifset = $manifest;
        $this->templateFactory = $templateFactory;
    }

    public function assemble()
    {
        $markup = "";
        $mainTemplate = $this->templateFactory->getTemplate($this->manifset->getTopLevelData());
        if ($mainTemplate) {
            $markup .= $mainTemplate->open() . $mainTemplate->body();
        }
        foreach ($this->manifset->getSections() as $section) {
            $sectionTemplate = $this->templateFactory->getTemplate($section);
            if ($sectionTemplate) {
                $markup .= $sectionTemplate->open() . $sectionTemplate->body();
            }
            foreach ($this->manifset->getChildren($section) as $child) {
                $childTemplate = $this->templateFactory->getTemplate($child);
                if ($childTemplate) {
                    $markup .= $childTemplate->open() . $childTemplate->body() . $childTemplate->close();
                }
            }
            $markup .= $sectionTemplate->close();
        }
        $mainTemplate->close();
        $markup .= $mainTemplate->close();

        return $markup;
    }

}

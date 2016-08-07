<?php
namespace JHM;

interface ManifestInterface
{

    public function getTopLevelData();

    public function getSections();

    public function getChildren(array $section);
}

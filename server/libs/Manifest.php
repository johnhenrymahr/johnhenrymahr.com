<?php
namespace JHM;

class Manifest implements ManifestInterface
{

    protected $json = [];

    public function __construct(FileLoaderInterface $fileLoader)
    {
        $this->json = $fileLoader->load('viewManifest.json', true);
        if (!is_array($this->json) || empty($this->json)) {
            throw new JhmException('Manifest is malformed or empty');
        }
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->json)) {
            return $this->json[$key];
        }
        return false;
    }

    public function getTopLevelData()
    {
        return array_diff_key($this->json, ["sections" => [], "children" => []]);
    }

    public function getSections()
    {
        if (array_key_exists('sections', $this->json) && is_array($this->json['sections'])) {
            return $this->json['sections'];
        }
        return [];
    }

    public function getChildren(array $section)
    {
        if (array_key_exists('children', $section) && is_array($section['children'])) {
            return $section['children'];
        }
        return [];
    }

}

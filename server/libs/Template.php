<?php
namespace JHM;

class Template implements TemplateInterface
{

    protected $data = [];

    protected $attributes = [];

    protected $id;

    protected $tagName = 'div';

    protected $content = '';

    protected $_open = false;

    public function __construct(array $data, \QueryPath\DOMQuery $content)
    {

        $this->content = $content;

        $this->data = $data;

        if (array_key_exists('attributes', $data) && is_Array($data['attributes'])) {
            $this->attributes = $data['attributes'];
        }

        if (array_key_exists('tagName', $this->attributes)) {
            $this->tagName = $this->attributes['tagName'];
            unset($this->attributes['tagName']);
        }

        if (array_key_exists('tagName', $data)) {
            $this->tagName = $data['tagName'];
        }

        if (array_key_exists('id', $data)) {
            $this->id = $data['id'];
        }

    }

    public function open()
    {
        $this->_open = true;
        return $this->_buildTag();
    }

    public function body()
    {
        return $this->content->find('body')->innerHTML5();
    }

    public function close()
    {
        $this->_open = false;
        return '</' . $this->tagName . '>';
    }

    public function markup()
    {
        return $this->open() . $this->body() . $this->close();
    }

    public function isOpen()
    {
        return $this->_open;
    }

    public function appendChild(TemplateInterface $template)
    {
        $ref = $this->_getChildViewContainer();
        if ($ref) {
            $ref->append($template->markup());
            return $ref;
        }
        return false;
    }

    protected function _getChildViewContainer()
    {
        if (array_key_exists('childViewContainer', $this->data)
            && is_string($this->data['childViewContainer'])
            && !empty($this->data['childViewContainer'])) {

            $ref = $this->content->find($this->data['childViewContainer']);

            if (!$ref->count()) {
                return false;
            }
            return $ref;
        } elseif ($this->content->find('body')->contents()->count()) {
            return $this->content->find('body')->contents();
        } else {
            return $this->content->find('body');
        }
    }

    protected function _buildTag()
    {
        $tag = '<' . $this->tagName;
        foreach ($this->attributes as $key => $val) {
            if ($key === "className") {
                $key = "class";
            }
            $tag .= ' ' . $key . '="' . $val . '"';
        }
        $tag .= '>';
        return $tag;
    }

}

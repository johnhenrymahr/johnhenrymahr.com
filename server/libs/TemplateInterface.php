<?php
namespace JHM;

interface TemplateInterface
{

    /**
     * __construct
     * @param array  $data   manifest data
     * @param \QueryPath\DOMQuery $content
     * @param boolean  $bareElement true if element is just text
     */
    public function __construct(array $data, \QueryPath\DOMQuery $content, $bareElement = false);
    /**
     * open
     * @return string opening tag with rendered attributes
     */
    public function open();
    /**
     * body
     * @return string body content
     */
    public function body();
    /**
     * close
     * @return string closing tag
     */
    public function close();
    /**
     * markup
     * calls open + body + close
     * @return string all markup
     */
    public function markup();
    /**
     * appendChild
     * append child templates
     * @param  Template $template
     * @return boolean fale failure
     * @return  \QueryPath\DOMQuery container reference
     */
    public function appendChild(TemplateInterface $template);
    /**
     * is Open
     * has the template element been closed
     * @return boolean
     */
    public function isOpen();
}

<?php
namespace JHM;

interface TemplateInterface
{

    /**
     * __construct
     * @param array  $data   manifest data
     * @param string $content
     */
    public function __construct(array $data, $content);
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

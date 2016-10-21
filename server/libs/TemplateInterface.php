<?php
namespace JHM;

interface TemplateInterface
{
    public function open();
    public function body();
    public function close();
    public function markup();
    public function appendChild(Template $template);
}

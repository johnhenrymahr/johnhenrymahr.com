<?php
namespace JHM;
interface RendererInterface {
	public function compileFile($templatePath);
	public function renderTemplate($template, $data);
}
?>
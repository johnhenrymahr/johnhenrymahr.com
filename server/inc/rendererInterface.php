<?php 
interface RendererInterface {
	public function compile($templateString);
	public function renderTemplate($template, $data);
}
?>
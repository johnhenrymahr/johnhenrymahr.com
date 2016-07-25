<?php
namespace JHM;
interface manifestInterface {

	public function getTopLevelData();

	public function getSections();

	public function getChildren(array $section);
}
?>
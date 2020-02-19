<?php namespace eFilter\Interfaces;

interface DescendantsBuilderInterface
{
	public function setParam($name, $value);
	
	public function buildChildren();
	
	public function getChildren();
}

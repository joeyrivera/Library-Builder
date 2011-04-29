<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initPaths()
	{
		$paths = $this->getOption('paths');
		
		Zend_Registry::set('paths', $paths);
	}
}
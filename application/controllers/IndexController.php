<?php

class IndexController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$form = new App_Form_Site();
		
		if($this->getRequest()->isPost())
		{
			$params = $this->getRequest()->getParams();
			
			if($form->isValid($params))
			{
				$builder = new App_Model_Builder($params);
				$builder->build();
			}
		}
		
		$this->view->form = $form;
	}
}
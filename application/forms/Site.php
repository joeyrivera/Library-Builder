<?php
class App_Form_Site extends Zend_Form
{
	public function __construct($options = null)
	{
		parent::__construct($options);
		
		$this->setName('site');
		$this->setMethod('post');
		
		$name = new Zend_Form_Element_Text('name');
		$name->setLabel('Site Name')
			->setRequired(true)
			->addFilter('StringTrim')
			->addFilter('StripTags');

		$author = new Zend_Form_Element_Text('author');
		$author->setLabel('Author')
			->setRequired(true)
			->addFilter('StringTrim')
			->addFilter('StripTags');
			
		$entity = new Zend_Form_Element_MultiCheckbox('files');
		$entity->setLabel('Choose all types of files you want to create:')
			->setMultiOptions(array(
				'ENTITY' => 'Entity Models',
				'MAPPER' => 'Data Mapper',
				'DBTABLE' => 'Zend Db Table',
				'METADATA' => 'Metadata Mappers'
		));
				
		$site_form = new Zend_Form_SubForm();
		$site_form->addElements(array(
			$name,
			$author,
			$entity
		));
		
		$this->addSubForm($site_form, 'site');
		
		$host = new Zend_Form_Element_Text('host');
		$host->setLabel('Host')
			->setRequired(true)
			->addFilter('StringTrim')
			->addFilter('StripTags');
			//->addValidator('HostName', false, Zend_Validate_Hostname::ALLOW_ALL);
			
		$username = new Zend_Form_Element_Text('username');
		$username->setLabel('Username')
			->setRequired(true)
			->addFilter('StringTrim')
			->addFilter('StripTags');
			
		$password = new Zend_Form_Element_Password('password');
		$password->setLabel('Password')
			->setRequired(true)
			->addFilter('StringTrim')
			->addFilter('StripTags');
		
		$database = new Zend_Form_Element_Text('dbname');
		$database->setLabel('Database Name')
			->setRequired(true)
			->addFilter('StringTrim')
			->addFilter('StripTags');

		$adapter = new Zend_Form_Element_Select('adapter');
		$adapter->setLabel('Database Adapter')
			->setRequired(true)
			->addFilter('StringTrim')
			->addFilter('StripTags')
			->setMultiOptions(array(
				'pdo_mysql' => 'MySQL',
				'pdo_pgsql' => 'PostgreSQL',
				'sqlsrv' => 'MSSQL'
			)
		);
			
		// think about options
		$db_form = new Zend_Form_SubForm();
		$db_form->addElements(array(
			$host,
			$username,
			$password,
			$database,
			$adapter
		));
		
		$this->addSubForm($db_form, 'db');
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Submit')
			->setIgnore(true);
			
		$this->addElement($submit);
	}
}
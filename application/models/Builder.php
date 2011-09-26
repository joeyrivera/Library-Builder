<?php
class App_Model_Builder
{
	protected $_paths;
	protected $_db;
	protected $_db_options = array();
	protected $_site_options = array();
	
	public function __construct($options = null)
	{
		$this->_paths = Zend_Registry::get('paths');

		foreach($options as $key => $value)
		{
			if($key == 'db')
			{
				$this->_db_options = $value;
			}
			elseif($key == 'site')
			{
				$this->_site_options = $value;
			}
		}
		
		// make sure all paths are set
		$this->_paths['site']['root'] = $this->_paths['sites'] . $this->_site_options['name'];
		$this->_paths['site']['model'] = $this->_paths['site']['root'] . '/Model/';
		$this->_paths['site']['mapper'] = $this->_paths['site']['model'] . '/Mapper/';
		$this->_paths['site']['metadata'] = $this->_paths['site']['mapper'] . '/Metadata/';
		$this->_paths['site']['db_table'] = $this->_paths['site']['model'] . '/DbTable/';
		
		$this->_paths['ns']['model'] = $this->_site_options['name'] . '_Model_';
		$this->_paths['ns']['mapper'] = $this->_paths['ns']['model'] . 'Mapper_';
		$this->_paths['ns']['metadata'] = $this->_paths['ns']['mapper'] . 'Metadata_';
		$this->_paths['ns']['db_table'] = $this->_paths['ns']['model'] . 'DbTable_';
	}
	
	public function build()
	{
		// make sure db works
		try 
		{
			$adapter = $this->_db_options['adapter'];
			
			// some adapters complain if passing a none-valid option
			unset($this->_db_options['adapter']);
			
			$this->_db = Zend_Db::factory($adapter, $this->_db_options);
			
			$tables = $this->_db->listTables();
		}
		catch(Exception $e)
		{
			exit($e->getMessage());
		}
		
		// make sure we can write
		if(!is_writable($this->_paths['sites']))
		{
			exit("Can't write to path.");
		}
		
		// check paths
		foreach($this->_paths['site'] as $path)
		{
			if(!is_dir($path))
			{
				mkdir($path, null, true);
			}
		}

		// create each entity
		$entities = array();
		foreach($tables as $table)
		{
			if(array_search('ENTITY', $this->_site_options['files']) !== false)
			{
				$entity = $this->_createEntity($table, $this->_tableNameToClassName($table));
				file_put_contents($this->_paths['site']['model'] . $entity->getFilename(), $entity->generate());
			}
			
			if(array_search('MAPPER', $this->_site_options['files']) !== false)
			{
				$mapper = $this->_createMapper($this->_tableNameToClassName($table));
				file_put_contents($this->_paths['site']['mapper'] . $mapper->getFilename(), $mapper->generate());
			}
			
			if(array_search('METADATA', $this->_site_options['files']) !== false)
			{
				$metadata = $this->_createMetadata($table, $this->_tableNameToClassName($table));
				file_put_contents($this->_paths['site']['metadata'] . $metadata->getFilename(), $metadata->generate());
			}
			
			if(array_search('DBTABLE', $this->_site_options['files']) !== false)
			{
				$db_table = $this->_createDbTable($table, $this->_tableNameToClassName($table));
				file_put_contents($this->_paths['site']['db_table'] . $db_table->getFilename(), $db_table->generate());
			}
		}
	}
	
	protected function _createEntity($table, $name)
	{
		$details = $this->_db->describeTable($table);
		
		// get columns
		$properties = array();
		foreach($details as $key => $value)
		{
			$properties[$key] = (bool)$value['IDENTITY'] ? 0 : null;
		}
		
		$class = new Zend_CodeGenerator_Php_Class();
		$class->setName($this->_paths['ns']['model'] . $name)
			->setExtendedClass($this->_paths['ns']['model'] . 'Entity');
		
		$property = new Zend_CodeGenerator_Php_Property();
		$property->setName('_data')
			->setVisibility('protected')
			->setDefaultValue($properties);
		
		$class->setProperty($property);
		
		$docblock = new Zend_CodeGenerator_Php_Docblock();
		$docblock->setShortDescription("{$name} Entity.")
			->setTag(new Zend_CodeGenerator_Php_Docblock_Tag(array(
				'name' => 'author', 
				'description' => $this->_site_options['author'])
		));

		$class->setDocblock($docblock);	
			
		$file= new Zend_CodeGenerator_Php_File();
		$file->setClass($class)
			->setFilename($name . '.php');
		
		return $file;
	}
	
	protected function _createMapper($name)
	{
		$class = new Zend_CodeGenerator_Php_Class();
		$class->setName($this->_paths['ns']['mapper'] . $name)
			->setExtendedClass($this->_paths['ns']['mapper'] . 'Abstract');
		
		$model_name = new Zend_CodeGenerator_Php_Property();
		$model_name->setName('_model_name')
			->setVisibility('protected')
			->setDefaultValue($this->_paths['ns']['model'] . $name);
			
		$db_table_name = new Zend_CodeGenerator_Php_Property();
		$db_table_name->setName('_db_table_name')
			->setVisibility('protected')
			->setDefaultValue($this->_paths['ns']['db_table'] . $name);
		
		$class->setProperties(array(
			$model_name,
			$db_table_name
		));
		
		$docblock = new Zend_CodeGenerator_Php_Docblock();
		$docblock->setShortDescription("{$name} Mapper in charge of creating entit(ies) of type {$name}.")
			->setTag(new Zend_CodeGenerator_Php_Docblock_Tag(array(
				'name' => 'author', 
				'description' => $this->_site_options['author'])
		));

		$class->setDocblock($docblock);	
			
		$file= new Zend_CodeGenerator_Php_File();
		$file->setClass($class)
			->setFilename($name . '.php');
		
		return $file;
	}
	
	protected function _createMetadata($table, $name)
	{
		$details = $this->_db->describeTable($table);
		
		// get columns
		$properties = array();
		foreach($details as $key => $value)
		{
			$properties[$key] = $key;
		}
		
		$class = new Zend_CodeGenerator_Php_Class();
		$class->setName($this->_paths['ns']['metadata'] . $name)
			->setExtendedClass($this->_paths['ns']['metadata'] . 'Abstract');
		
		$property = new Zend_CodeGenerator_Php_Property();
		$property->setName('_data')
			->setVisibility('protected')
			->setDefaultValue($properties);
		
		$class->setProperty($property);
		
		$docblock = new Zend_CodeGenerator_Php_Docblock();
		$docblock->setShortDescription("{$name} Metadata Mapper. Allows overriding of table to entity field to property naming convension.")
			->setTag(new Zend_CodeGenerator_Php_Docblock_Tag(array(
				'name' => 'author', 
				'description' => $this->_site_options['author'])
		));

		$class->setDocblock($docblock);	
			
		$file= new Zend_CodeGenerator_Php_File();
		$file->setClass($class)
			->setFilename($name . '.php');
		
		return $file;
	}
	
	protected function _createDbTable($table, $name)
	{
		$class = new Zend_CodeGenerator_Php_Class();
		$class->setName($this->_paths['ns']['db_table'] . $name)
			->setExtendedClass($this->_paths['ns']['db_table'] . 'Abstract');
		
		$table_name = new Zend_CodeGenerator_Php_Property();
		$table_name->setName('_name')
			->setVisibility('protected')
			->setDefaultValue($table);
			
		$class->setProperty($table_name);
		
		$docblock = new Zend_CodeGenerator_Php_Docblock();
		$docblock->setShortDescription("{$name} DbTable Abstract.")
			->setTag(new Zend_CodeGenerator_Php_Docblock_Tag(array(
				'name' => 'author', 
				'description' => $this->_site_options['author'])
		));

		$class->setDocblock($docblock);	
			
		$file= new Zend_CodeGenerator_Php_File();
		$file->setClass($class)
			->setFilename($name . '.php');
		
		return $file;
	}
	
	protected function _tableNameToClassName($table_name)
	{
		$class_name = $table_name;
		
		$class_name = str_replace('_', ' ', $class_name);
		$class_name = ucwords($class_name);
		$class_name = str_replace(' ', '', $class_name);
		
		// make table suffix/prefix options
		$class_name = str_replace('Tbl', '', $class_name);
		
		return $class_name;
	}
}
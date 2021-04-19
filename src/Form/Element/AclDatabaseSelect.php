<?php 
namespace Components\Form\Element;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Select as SqlSelect;
use Exception;
use Components\Traits\AclAwareTrait;

class AclDatabaseSelect extends DatabaseSelect
{
    use AclAwareTrait;
    
    public $roles;
    public $privilege = 'list';
    
    protected $acl_resource_column = 'RESOURCE';
    
    public function populateElement()
    {
        if (!isset($this->adapter)) {
            throw new Exception('Missing Adapter in Options');
        }
        
        
        $sql = new Sql($this->adapter);
        
        if (is_null($this->database_object)) {
            $this->database_object = new SqlSelect();
            $this->database_object->from($this->database_table);
        }
        
        /**
         * Combine Columns
         * 
         * Columns parameter should only include those being displayed in drop
         * down, but we need to retrieve additional hidden columns for functionality.
         */
        $columns = $this->database_value_columns;
        array_unshift($columns, $this->acl_resource_column);
        array_unshift($columns, $this->database_id_column);
        $this->database_object->columns($columns);
        
        $this->database_object->order(next($columns));

        
        $statement = $sql->prepareStatementForSqlObject($this->database_object);
        
        try {
            $resultSet = $statement->execute();
        } catch (Exception $e) {
            return $e;
        }
        
        $options = [];
        $options[NULL] = '--- Unassigned ---';
        foreach ($resultSet as $object) {
            $id = $object[$this->database_id_column];
            array_shift($object);
            
            $resource = $object[$this->acl_resource_column];
            array_shift($object);
            
            if ($this->isAllowed($this->roles, $resource, $this->privilege)) {
                $options[$id] = implode(', ', $object);
            }
        }
        
        $this->setValueOptions($options);
        
        return $this;
    }
    
    public function setOptions($options)
    {
        if (isset($options['acl_service'])) {
            $this->setAclService($options['acl_service']);
        }
        
        if (isset($options['acl_resource_column'])) {
            $this->setAclResourceField($options['acl_resource_column']);
        }
        
        parent::setOptions($options);

        return $this;
    }
    
    public function getAclResourceField()
    {
        return $this->acl_resource_column;
    }
    
    public function setAclResourceField($acl_resource_column)
    {
        $this->acl_resource_column = $acl_resource_column;
        return $this;
    }
    
    
}

<?php
namespace Components\Form\Element;

use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Sql\Select as SqlSelect;
use Laminas\Db\Sql\Sql;
use Laminas\Form\Element\Select;
use Exception;

class DatabaseSelect extends Select
{
    use AdapterAwareTrait;
    
    protected $database_table;
    protected $database_id_column;
    protected $database_value_columns;
    
    public function init()
    {
        $valueOptions = [
            1 => 'One',
            2 => 'Two',
        ];
        
        $this->setValueOptions($valueOptions);
    }
    
    public function populateElement()
    {
        if (!isset($this->adapter)) {
            throw new Exception('Missing Adapter in Options');
        }
        
        $sql = new Sql($this->adapter);
        
        $select = new SqlSelect();
        $select->from($this->database_table);
        
        
        $columns = $this->database_value_columns;
        array_unshift ($columns, $this->database_id_column);
        
        $select->columns($columns);
        
        /** ORDER BY the first value column **/
        $select->order(next($columns));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
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
            
            $options[$id] = implode(', ', $object);
        }
        
        $this->setValueOptions($options);
    }
    
    public function setOptions($options)
    {
        parent::setOptions($options);
        
        if (isset($options['database_table'])) {
            $this->setDatabase_table($options['database_table']);
        }
        
        if (isset($options['database_id_column'])) {
            $this->setDatabase_id_column($options['database_id_column']);
        }
        
        if (isset($options['database_value_columns'])) {
            $this->setDatabase_value_columns($options['database_value_columns']);
        }
        
        if (isset($options['database_adapter'])) {
            $this->setDbAdapter($options['database_adapter']);
        }
        
        $this->populateElement();
        
        return $this;
    }
    
    public function getDatabase_table()
    {
        return $this->database_table;
    }
    
    public function setDatabase_table($database_table)
    {
        $this->database_table = $database_table;
        return $this;
    }
    
    public function getDatabase_id_column()
    {
        return $this->database_id_column;
    }
    
    public function setDatabase_id_column($database_id_column)
    {
        $this->database_id_column = $database_id_column;
        return $this;
    }
    
    public function getDatabase_value_columns()
    {
        return $this->database_value_columns;
    }
    
    public function setDatabase_value_columns($database_value_columns)
    {
        $this->database_value_columns = $database_value_columns;
        return $this;
    }
}
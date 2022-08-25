<?php
namespace Components\Model;

use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Predicate\Predicate;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Exception;

abstract class AbstractBaseModel implements InputFilterAwareInterface
{
    use AdapterAwareTrait;
    
    const INACTIVE_STATUS = 2;
    const ACTIVE_STATUS = 1;
    
    protected $table;
    protected $inputFilter;
    protected $private_attributes;
    protected $public_attributes;
    protected $primary_key;
    protected $required;
    protected $select;
    
    public $UUID;
    public $STATUS;
    public $DATE_CREATED;
    public $DATE_MODIFIED;
    
    public function __construct($adapter = NULL)
    {
        $this->setDbAdapter($adapter);
        
        $this->private_attributes = [
            'adapter',                  //-- From AdapterAwareTrait --//
            'table',
            'inputFilter',
            'private_attributes',
            'public_attributes',
            'primary_key',
            'required',
            'current_user',
            'select',
        ];
        $this->UUID = $this->generate_uuid();
        $this->setPrimaryKey('UUID');
        
        $date = new \DateTime('now',new \DateTimeZone('UTC'));
        $today = $date->format('Y-m-d H:i:s');
        $this->DATE_CREATED = $today;
        
        $this->STATUS = $this::ACTIVE_STATUS;
        
        $this->public_attributes = array_diff(array_keys(get_object_vars($this)), $this->private_attributes);
    }
    
    public function exchangeArray($data)
    {
        foreach ($this->public_attributes as $var) {
            if (!empty($data[$var])) {
                $this->$var = $data[$var];
            }
        }
    }
    
    public function getArrayCopy()
    {
        $data = NULL;
        foreach ($this->public_attributes as $var) {
            $data[$var] = $this->{$var};
        }
        return $data;
    }
    
    public function getTableName()
    {
        return $this->table;
    }
    
    public function setTableName($table)
    {
        $this->table = $table;
        return $this;
    }
    
    public function getPrimaryKey()
    {
        return $this->primary_key;
    }
    
    public function setPrimaryKey($primary_key)
    {
        $this->primary_key = $primary_key;
        return $this;
    }
    
    public static function retrieveStatus($status)
    {
        $statuses = [
            NULL => 'Inactive',
            self::INACTIVE_STATUS => 'Inactive',
            self::ACTIVE_STATUS => 'Active',
        ];
        
        return $statuses[$status];
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new Exception("Not Used");
    }
    
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            
            foreach ($this->public_attributes as $var) {
                $inputFilter->add([
                    'name' => $var,
                    'required' => $this->required,
                    'filters' => [
                        ['name' => StripTags::class],
                        ['name' => StringTrim::class],
                    ],
                ]);
            }
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
    
    public function fetchAll(Predicate $predicate = null, array $order = [])
    {
        if ($predicate == null) {
            $predicate = new Where();
        }
        
        $sql = new Sql($this->adapter);
        
        $select = $this->getSelect();
        $select->from($this->getTableName());
        $select->where($predicate);
        $select->order($order);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        try {
            $results = $statement->execute();
            $resultSet->initialize($results);
        } catch (Exception $e) {
            return FALSE;
        }
        
        return $resultSet->toArray();
    }
    
    public function create()
    {
        $date = new \DateTime('now',new \DateTimeZone('UTC'));
        $this->DATE_CREATED = $date->format('Y-m-d H:i:s');
        
        if (is_null($this->UUID)) {
            $this->UUID = $this->generate_uuid();
        }
        
        $sql = new Sql($this->adapter);
        $values = $this->getArrayCopy();
        
        $insert = new Insert();
        $insert->into($this->getTableName());
        $insert->values($values);
        
        $statement = $sql->prepareStatementForSqlObject($insert);
        
        try {
            $statement->execute();
        } catch (Exception $e) {
            return FALSE;
        }
        return TRUE;
    }
    
    public function read(Array $criteria)
    {
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select->from($this->getTableName());
        $select->where($criteria);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        try {
            $resultSet = $statement->execute();
        } catch (Exception $e) {
            return FALSE;
        }
        
        if ($resultSet->getAffectedRows() == 0) {
            return FALSE;
        } else {
            $this->exchangeArray($resultSet->current());
            return TRUE;
        }
    }
    
    public function update()
    {
        $date = new \DateTime('now',new \DateTimeZone('UTC'));
        $this->DATE_MODIFIED = $date->format('Y-m-d H:i:s');
        
        $sql = new Sql($this->adapter);
        $values = $this->getArrayCopy();
        
        $update = new Update();
        $update->table($this->getTableName());
        $update->set($values);
        $update->where([$this->primary_key => $values[$this->primary_key]]);
        
        $statement = $sql->prepareStatementForSqlObject($update);
        
        try {
            $statement->execute();
        } catch (Exception $e) {
            return FALSE;
        }
        return TRUE;
    }
    
    public function delete()
    {
        $prikey = $this->primary_key;
        
        $sql = new Sql($this->adapter);
        
        $delete = new Delete();
        $delete->from($this->getTableName())->where(array($prikey => $this->$prikey));
        $statement = $sql->prepareStatementForSqlObject($delete);
        
        try {
            $statement->execute();
        } catch (Exception $e) {
            return FALSE;
        }
        return TRUE;
    }
    
    public function generate_uuid()
    {
        return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 4095),
            bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)
            );
    }
    
    /**
     * @return \Laminas\Db\Sql\Select
     */
    public function getSelect()
    {
        if (!is_a($this->select, Select::class)) {
            $this->select = new Select();
        }
        return $this->select;
    }

    /**
     * @param Select $select
     */
    public function setSelect(Select $select)
    {
        $this->select = $select;
    }

}
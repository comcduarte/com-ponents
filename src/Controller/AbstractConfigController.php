<?php 
namespace Components\Controller;

use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Ddl\DropTable;
use Laminas\Db\Sql\Ddl\SqlInterface;
use Laminas\Db\Sql\Ddl\Column\Datetime;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Validator\Db\RecordExists;
use Laminas\View\Model\ViewModel;
use Settings\Model\SettingsModel;
use Exception;

abstract class AbstractConfigController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    private $route;
    private $config;
    
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('base/config');
        $view->setVariables([
            'route' => $this->getRoute(),
        ]);
        return ($view);
    }
    
    public function clearAction()
    {
        $this->clearDatabase();
        $this->flashMessenger()->addSuccessMessage("Database tables cleared.");
        return $this->redirect()->toRoute($this->getRoute());
    }
    
    public function createAction()
    {
        $this->checkSettingsDatabase();
        $this->createDatabase();
        $this->flashMessenger()->addSuccessMessage("Database tables populated.");
        return $this->redirect()->toRoute($this->getRoute());
    }
    
    public function checkSettingsDatabase()
    {
        $validator = new RecordExists([
            'table' => 'settings',
            'field' => 'MODULE',
            'adapter' => $this->adapter,
        ]);
        
        try {
            $validator->isValid('INSTALLED');
            $this->flashMessenger()->addSuccessMessage("Database exists.");
            return TRUE;
        } catch (Exception $e) {
            $this->flashMessenger()->addErrorMessage("Database does not exist.");
            return FALSE;
        }
    }
    
    public function createSettingsDatabase()
    {
        if ($this->checkSettingsDatabase()) {
            return TRUE;
        }
        
        $sql = new Sql($this->adapter);
        
        /******************************
         * ACL
         ******************************/
        $ddl = new CreateTable('settings');
        
        $ddl->addColumn(new Varchar('UUID', 36));
        $ddl->addColumn(new Integer('STATUS', TRUE));
        $ddl->addColumn(new Datetime('DATE_CREATED', TRUE));
        $ddl->addColumn(new Datetime('DATE_MODIFIED', TRUE));
        
        $ddl->addColumn(new Varchar('MODULE', 255));
        $ddl->addColumn(new Varchar('SETTING', 255));
        $ddl->addColumn(new Varchar('VALUE', 255, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->adapter->query($sql->buildSqlString($ddl), $this->adapter::QUERY_MODE_EXECUTE);
        unset($ddl);
        
        /******************************
         * Create Default Setting
         ******************************/
        $setting = new SettingsModel($this->adapter);
        $setting->MODULE = 'SETTINGS';
        $setting->SETTING = 'INSTALLED';
        $setting->VALUE = 'TRUE';
        $setting->create();
        unset ($setting);
    }
    
    public function clearSettingsDatabase()
    {
        $sql = new Sql($this->adapter);
        $ddl = [];
        
        $ddl[] = new DropTable('settings');
        
        foreach ($ddl as $obj) {
            $this->adapter->query($sql->buildSqlString($obj), $this->adapter::QUERY_MODE_EXECUTE);
        }
    }
    
    public function createSettings($module)
    {
        return $this->createSettingsDatabase();
    }
    
    public function clearSettings($module)
    {
        $sql = new Sql($this->adapter);
        
        $delete = new Delete();
        $delete->from('settings')->where(['MODULE' => $module]);
        $statement = $sql->prepareStatementForSqlObject($delete);
        
        try {
            $statement->execute();
        } catch (Exception $e) {
            return FALSE;
        }
        return TRUE;
    }
    
    public function getRoute()
    {
        return $this->route;
    }
    
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
    
    public function addStandardFields(SqlInterface $ddl)
    {
        $ddl->addColumn(new Varchar('UUID', 36));
        $ddl->addColumn(new Integer('STATUS', TRUE));
        $ddl->addColumn(new Datetime('DATE_CREATED', TRUE));
        $ddl->addColumn(new Datetime('DATE_MODIFIED', TRUE));
        
        return $ddl;
    }
    
    public function processDdl($ddl)
    {
        $sql = new Sql($this->adapter);
        try {
            $this->adapter->query($sql->buildSqlString($ddl), $this->adapter::QUERY_MODE_EXECUTE);
        } catch (\PDOException $e) {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
        }
        return;
    }
    
    abstract public function createDatabase();
    
    abstract public function clearDatabase();
}
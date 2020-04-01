<?php 
namespace Components\Controller;

use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Ddl\DropTable;
use Laminas\Db\Sql\Ddl\Column\Datetime;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

abstract class AbstractConfigController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    private $route;
    
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
        
    }
    
    public function createSettingsDatabase()
    {
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
        
    }
    
    public function clearSettings($module)
    {
        
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
    
    abstract public function createDatabase();
    
    abstract public function clearDatabase();
}
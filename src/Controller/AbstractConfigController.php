<?php 
namespace Components\Controller;

use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

abstract class AbstractConfigController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    private $route;
    
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('config');
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
        $this->createDatabase();
        $this->flashMessenger()->addSuccessMessage("Database tables populated.");
        return $this->redirect()->toRoute($this->getRoute());
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
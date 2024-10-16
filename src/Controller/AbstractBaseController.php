<?php 
namespace Components\Controller;

use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Sql\Where;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

abstract class AbstractBaseController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public $model;
    public $form;
    
    public function indexAction()
    {
        $this->getEventManager()->trigger('index.pre', $this, ['model' => $this->model, 'form' => $this->form]);
        
        $view = new ViewModel();
        $view->setTemplate('base/subtable');
        
        $data = $this->model->fetchAll(new Where());
        
        $header = [];
        if (!empty($data)) {
            $header = array_keys($data[0]);
        }
        
        $route = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
        
        $params = [
            [
                'route' => $route,
                'action' => 'update',
                'key' => 'UUID',
                'label' => 'Update',
            ],
            [
                'route' => $route,
                'action' => 'delete',
                'key' => 'UUID',
                'label' => 'Delete',
            ],
        ];
        
        $view->setvariables ([
            'route' => $route,
            'params' => $params,
            'data' => $data,
            'header' => $header,
            'primary_key' => $this->model->getPrimaryKey(),
            'title' => 'Index',
        ]);
        
        $this->getEventManager()->trigger('index.post', $this, ['view' => $view]);
        
        return $view;
    }
    
    public function createAction()
    {
        $this->getEventManager()->trigger('create.pre', $this, ['model' => $this->model, 'form' => $this->form]);
        
        $view = new ViewModel();
        $view->setTemplate('base/create');
        
        $request = $this->getRequest();
        $this->form->bind($this->model);
        
        if ($request->isPost()) {
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            
            $this->form->setData($post);
            
            if ($this->form->isValid()) {
                $this->model->create();
                
                $this->flashmessenger()->addSuccessMessage('Add New Record Successful');
            } else {
                foreach ($this->form->getMessages() as $message) {
                    if (is_array($message)) {
                        $message = array_pop($message);
                    }
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
            
            $this->getEventManager()->trigger('create.post', $this, ['view' => $view]);
            
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        
        $view->setVariables([
            'form' => $this->form,
            'title' => 'Add New Record',
        ]);
        
        $this->getEventManager()->trigger('create.post', $this, ['view' => $view]);
        
        return $view;
    }
    
    public function updateAction()
    {
        $this->getEventManager()->trigger('update.pre', $this, ['model' => $this->model, 'form' => $this->form]);
        
        $primary_key = $this->params()->fromRoute(strtolower($this->model->getPrimaryKey()),0);
        if (!$primary_key) {
            $this->flashmessenger()->addErrorMessage("Unable to retrieve record. Value not passed.");
            
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        
        $view = new ViewModel();
        $view->setTemplate('base/update');
        
        $this->model->read([$this->model->getPrimaryKey() => $primary_key]);
        
        $this->form->bind($this->model);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            $this->form->setData($data);
            
            if ($this->form->isValid()) {
                $this->model->update();
                
                $this->flashmessenger()->addSuccessMessage('Update Successful');
                
                $url = $this->getRequest()->getHeader('Referer')->getUri();
                return $this->redirect()->toUrl($url);
            } else {
                foreach ($this->form->getMessages() as $message) {
                    if (is_array($message)) {
                        $message = array_pop($message);
                    }
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
        }
        
        $view->setVariables([
            'form' => $this->form,
            'title' => 'Update Record',
            'primary_key' => $this->model->getPrimaryKey(),
        ]);
        
        $this->getEventManager()->trigger('update.post', $this, ['view' => $view]);
        
        return ($view);
    }
    
    public function deleteAction()
    {
        $this->getEventManager()->trigger('delete.pre', $this, ['model' => $this->model, 'form' => $this->form]);
        
        $view = new ViewModel();
        $view->setTemplate('base/delete');
        
        $primary_key = $this->getPrimaryKey();
        $this->model->read([$this->model->getPrimaryKey() => $primary_key]);
        $this->form->bind($this->model);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');
            
            if ($del == 'Yes') {
                $this->model->delete();
            }
            
            return $this->redirect()->toUrl($request->getPost('referring_url'));
        }
        
        $view->setVariables([
            'model', $this->model,
            'form' => $this->form,
            'primary_key' => $this->model->getPrimaryKey(),
            'referring_url' => $this->getRequest()->getHeader('Referer')->getUri(),
        ]);
        
        $this->getEventManager()->trigger('delete.post', $this, ['view' => $view]);
        
        return ($view);
    }
    
    public function getModel()
    {
        return $this->model;
    }
    
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }
    
    public function getForm()
    {
        return $this->form;
    }
    
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }
    
    public function getPrimaryKey()
    {
        $primary_key = $this->params()->fromRoute(strtolower($this->model->getPrimaryKey()),0);
        if (!$primary_key) {
            $this->flashmessenger()->addErrorMessage("Unable to retrieve record. Value not passed.");
            
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        return $primary_key;
    }
}
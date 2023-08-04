<?php
namespace Components\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class Subtable extends AbstractHelper
{
    public $title;
    public $data;
    public $form;
    public $primary_key;
    public $params;
    public $help;
    
    public function __invoke(array $values)
    {
        foreach ($values as $var => $val) {
            $this->$var = $val;
        }
        return $this->render();
    }
    
    public function render()
    {
        $html = '';
        $html = $this->getView()->render('base/subtable', $this->getValues());
        return $html;
    }
    
    private function getValues()
    {
        $ary = [
            'title' => $this->title,
            'data' => $this->data,
            'form' => $this->form,
            'primary_key' => $this->primary_key,
            'params' => $this->params,
            'help' => $this->help,
        ];
        
        return $ary;
    }
}
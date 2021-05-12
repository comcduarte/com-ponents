<?php
namespace Components\Form\Element;


use Laminas\Form\Element;

class Treeview extends Element
{
    /**
     * Array of branches to display.
     * 
     * @var array
     */
    protected $data;
    
    /**
     * 
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * 
     * @param array $data
     * @return \Components\Form\Element\Treeview
     */
    public function setData(Array $data)
    {
        $this->data = $data;
        return $this;
    }
    
    public function setOptions($options)
    {
        parent::setOptions($options);
        
        if (isset($options['data'])) {
            $this->setData($options['data']);
        }
        
        return $this;
    }
}
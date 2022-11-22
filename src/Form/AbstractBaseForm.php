<?php
namespace Components\Form;

use Components\Form\Element\Uuid;
use Components\Model\AbstractBaseModel;
use Laminas\Form\Form;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Submit;

abstract class AbstractBaseForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'UUID',
            'type' => Uuid::class,
            'attributes' => [
                'id' => 'UUID',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'UUID',
            ],
        ],['priority' => 0]);
        
        $this->add([
            'name' => 'STATUS',
            'type' => Select::class,
            'attributes' => [
                'id' => 'STATUS',
                'class' => 'form-select',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Status',
                'value_options' => [
                    AbstractBaseModel::INACTIVE_STATUS => 'Inactive',
                    AbstractBaseModel::ACTIVE_STATUS => 'Active',
                ],
            ],
        ],['priority' => 10]);
        
        $this->add(new Csrf('SECURITY'),['priority' => 0]);
        
        $this->add([
            'name' => 'SUBMIT',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Submit',
                'class' => 'btn btn-primary form-control mt-4',
                'id' => 'SUBMIT',
            ],
        ],['priority' => 0]);
    }
}
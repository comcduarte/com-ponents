<?php
namespace Components\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Submit;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputFilter;

class UploadFileForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'FILE',
            'type' => File::class,
            'attributes' => [
                'id' => 'FILE',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Upload File',
            ],
        ]);
        
        $this->add(new Csrf('SECURITY'));
        
        $this->add([
            'name' => 'SUBMIT',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Submit',
                'class' => 'btn btn-primary',
                'id' => 'SUBMIT',
            ],
        ]);
    }
    
    public function addInputFilter()
    {
        $inputFilter = new InputFilter();
        
        $fileInput = new FileInput('FILE');
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            array(
                'target'    => './data/',
                'randomize' => true,
            )
            );
        $inputFilter->add($fileInput);
        
        $this->setInputFilter($inputFilter);
    }
}
<?php
namespace Components\Form\Element;

use Laminas\Form\Element;

class Uuid extends Element
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'hidden',
    ];
    
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
        
        $this->value = sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 4095),
            bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)
            );
    }
}

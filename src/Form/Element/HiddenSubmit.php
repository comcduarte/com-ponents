<?php
namespace Components\Form\Element;

use Laminas\Form\Element;

class HiddenSubmit extends Element
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'style' => 'display: none;',
        'type' => 'submit',
    ];
}
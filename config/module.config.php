<?php
use Components\Form\Element\AclDatabaseSelect;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'acl' => [
        'admin' => [
            'settings/default' => [],
        ],
    ],
    'form_elements' => [
        'factories' => [
            AclDatabaseSelect::class => InvokableFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            Components\Form\View\Helper\bsFormSelectButtonRow::class => InvokableFactory::class,
            Components\View\Helper\Subtable::class => InvokableFactory::class,
        ],
        'aliases' => [
            'bsFormSelectButtonRow' => Components\Form\View\Helper\bsFormSelectButtonRow::class,
            'subtable' => Components\View\Helper\Subtable::class,
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'base/index'    => __DIR__ . '/../view/components/base-views/index.phtml',
            'base/create'   => __DIR__ . '/../view/components/base-views/create.phtml',
            'base/update'   => __DIR__ . '/../view/components/base-views/update.phtml',
            'base/delete'   => __DIR__ . '/../view/components/base-views/delete.phtml',
            'base/config'   => __DIR__ . '/../view/components/base-views/config.phtml',
            'base/subtable' => __DIR__ . '/../view/components/base-views/subtable.phtml',
            'base/subform'  => __DIR__ . '/../view/components/base-views/subform.phtml',
            
            'bsFormSelectButton'    => __DIR__ . '/../view/components/bs-form-views/bsFormSelectButton.phtml',
            
            'navigation'            => __DIR__ . '/../view/components/partials/navigation.phtml',
            'flashmessenger'        => __DIR__ . '/../view/components/partials/flashmessenger.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
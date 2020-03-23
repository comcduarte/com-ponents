<?php

return [
    'view_manager' => [
        'template_map' => [
            'base/index' => __DIR__ . '/../view/components/base-views/index.phtml',
            'base/create' => __DIR__ . '/../view/components/base-views/create.phtml',
            'base/update' => __DIR__ . '/../view/components/base-views/update.phtml',
            'base/delete' => __DIR__ . '/../view/components/base-views/delete.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
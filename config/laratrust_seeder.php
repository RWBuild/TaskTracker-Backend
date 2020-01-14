<?php

return [
    'role_structure' => [
        'superadministrator' => [
            'users' => 'c,r,u,d',
            'roles' => 'c,r,u,d',
            'roles' => 'c,r,u,d'
        ],
        'administrator' => [
            'users' => 'c,r,u,d',
        ],
        'user' => [
            'profile' => 'c,r,u'
        ],
        'projectmanager' => [
            'location' => 'c,r,u,d',
            'roles' => 'c,r,u,d',
            'users' => 'c,r,u,d'
        ]
    ],
    'permission_structure' => [],
    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete'
    ]
];

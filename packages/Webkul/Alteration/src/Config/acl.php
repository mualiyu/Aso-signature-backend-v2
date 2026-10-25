<?php

return [
    [
        'key'   => 'alterations',
        'name'  => 'alteration::app.admin.acl.alterations',
        'route' => 'admin.alterations.claims.index',
        'sort'  => 6,
    ], [
        'key'   => 'alterations.claims',
        'name'  => 'alteration::app.admin.acl.claims',
        'route' => 'admin.alterations.claims.index',
        'sort'  => 1,
    ], [
        'key'   => 'alterations.claims.manage',
        'name'  => 'alteration::app.admin.acl.manage',
        'route' => 'admin.alterations.claims.update',
        'sort'  => 1,
    ], [
        'key'   => 'alterations.partners',
        'name'  => 'alteration::app.admin.acl.partners',
        'route' => 'admin.alterations.partners.index',
        'sort'  => 2,
    ], [
        'key'   => 'alterations.partners.create',
        'name'  => 'alteration::app.admin.acl.create',
        'route' => 'admin.alterations.partners.create',
        'sort'  => 1,
    ], [
        'key'   => 'alterations.partners.edit',
        'name'  => 'alteration::app.admin.acl.edit',
        'route' => 'admin.alterations.partners.edit',
        'sort'  => 2,
    ], [
        'key'   => 'alterations.partners.delete',
        'name'  => 'alteration::app.admin.acl.delete',
        'route' => 'admin.alterations.partners.delete',
        'sort'  => 3,
    ], [
        'key'   => 'alterations.fix_types',
        'name'  => 'alteration::app.admin.acl.fix-types',
        'route' => 'admin.alterations.fix_types.index',
        'sort'  => 3,
    ],
];

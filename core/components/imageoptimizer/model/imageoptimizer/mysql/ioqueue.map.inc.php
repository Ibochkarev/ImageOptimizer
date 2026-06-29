<?php

$xpdo_meta_map['ioQueue'] = [
    'package' => 'imageoptimizer',
    'version' => '1.1',
    'table' => 'imageoptimizer_queue',
    'extends' => 'xPDOSimpleObject',
    'tableMeta' => [
        'engine' => 'InnoDB',
    ],
    'fields' => [
        'source' => 0,
        'path' => '',
        'format' => 'webp',
        'width' => 0,
        'status' => 'pending',
        'skip_reason' => null,
        'original_size' => null,
        'converted_size' => null,
        'error' => null,
        'created_at' => null,
        'processed_at' => null,
        'locked_at' => null,
    ],
    'fieldMeta' => [
        'source' => [
            'dbtype' => 'int',
            'precision' => '10',
            'attributes' => 'unsigned',
            'phptype' => 'integer',
            'null' => false,
            'default' => 0,
        ],
        'path' => [
            'dbtype' => 'varchar',
            'precision' => '255',
            'phptype' => 'string',
            'null' => false,
            'default' => '',
        ],
        'format' => [
            'dbtype' => 'varchar',
            'precision' => '10',
            'phptype' => 'string',
            'null' => false,
            'default' => 'webp',
        ],
        'width' => [
            'dbtype' => 'int',
            'precision' => '10',
            'attributes' => 'unsigned',
            'phptype' => 'integer',
            'null' => false,
            'default' => 0,
        ],
        'status' => [
            'dbtype' => 'varchar',
            'precision' => '20',
            'phptype' => 'string',
            'null' => false,
            'default' => 'pending',
        ],
        'skip_reason' => [
            'dbtype' => 'varchar',
            'precision' => '64',
            'phptype' => 'string',
            'null' => true,
        ],
        'original_size' => [
            'dbtype' => 'int',
            'precision' => '10',
            'attributes' => 'unsigned',
            'phptype' => 'integer',
            'null' => true,
        ],
        'converted_size' => [
            'dbtype' => 'int',
            'precision' => '10',
            'attributes' => 'unsigned',
            'phptype' => 'integer',
            'null' => true,
        ],
        'error' => [
            'dbtype' => 'text',
            'phptype' => 'string',
            'null' => true,
        ],
        'created_at' => [
            'dbtype' => 'datetime',
            'phptype' => 'datetime',
            'null' => true,
        ],
        'processed_at' => [
            'dbtype' => 'datetime',
            'phptype' => 'datetime',
            'null' => true,
        ],
        'locked_at' => [
            'dbtype' => 'datetime',
            'phptype' => 'datetime',
            'null' => true,
        ],
    ],
    'indexes' => [
        'uniq_variant' => [
            'alias' => 'uniq_variant',
            'primary' => false,
            'unique' => true,
            'type' => 'BTREE',
            'columns' => [
                'source' => [],
                'path' => [],
                'format' => [],
                'width' => [],
            ],
        ],
        'status' => [
            'alias' => 'status',
            'primary' => false,
            'unique' => false,
            'type' => 'BTREE',
            'columns' => [
                'status' => [],
            ],
        ],
        'source' => [
            'alias' => 'source',
            'primary' => false,
            'unique' => false,
            'type' => 'BTREE',
            'columns' => [
                'source' => [],
            ],
        ],
        'path' => [
            'alias' => 'path',
            'primary' => false,
            'unique' => false,
            'type' => 'BTREE',
            'columns' => [
                'path' => [],
            ],
        ],
        'locked_at' => [
            'alias' => 'locked_at',
            'primary' => false,
            'unique' => false,
            'type' => 'BTREE',
            'columns' => [
                'locked_at' => [],
            ],
        ],
    ],
];

<?php

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params'      => [
                    'host'          => 'localhost',
                    'port'          => '3306',
                    'user'          => 'root',
                    'password'      => 'root',
                    'dbname'        => 'test',
                    'charset'       => 'utf8',
                    'driverOptions' => [
                        1002 => 'SET NAMES utf8'
                    ],
				]
			]
		]
	],
	'db' => [
		'driver'         	=> 'Pdo',
		'dsn'           	=> 'mysql:dbname=test;host=localhost',
		'user'          	=> 'root',
		'password'      	=> 'root',
		'driver_options' 	=> [
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
		],
	],
];
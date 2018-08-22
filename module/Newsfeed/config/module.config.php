<?php

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'doctrine'        => [
        'driver' => [
            'Newsfeed_entities' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Newsfeed/Entity'],
            ],
            'orm_default'       => [
                'drivers' => [
                    'Newsfeed\Entity' => 'Newsfeed_entities',
                ],
            ],
        ],
    ],
    'controllers'     => [
        'factories'	=> [
            Newsfeed\Controller\Setup::class	        =>	Newsfeed\Controller\Factory\Controller::class,
            Newsfeed\Controller\Login::class	        =>	Newsfeed\Controller\Factory\Controller::class,
            Newsfeed\Controller\Dashboard::class	    =>	Newsfeed\Controller\Factory\Controller::class,
            Newsfeed\Controller\Feedmanager::class	    =>	Newsfeed\Controller\Factory\Controller::class,
            Newsfeed\Controller\User::class	            =>	Newsfeed\Controller\Factory\Controller::class,
        ],
    ],
    'service_manager' => [
        'invokables'   => [
            Newsfeed\Entity\Question::class,
        ],
        'factories' => [
            \Newsfeed\Service\Authentication::class     => \Newsfeed\Service\Factory\Authentication::class,
        ],
    ],
	'router' => [
		'routes' => [
			'application' => [
				'type'          => Literal::class,
				'options'       => [
					'route'    => '/',
					'defaults' => [
						'__NAMESPACE__' => 'Newsfeed\Controller',
						'controller'    => 'Dashboard',
						'action'        => 'index',
					],
				],
				'may_terminate' => true,
				'child_routes'  => [
					'default' => [
						'type'    => Segment::class,
						'options' => [
							'route'       => '[:controller[/:action]]',
							'constraints' => [
								'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
								'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
							],
							'defaults'    => [
								'__NAMESPACE__' => 'Newsfeed\Controller',

							],
						],
					],
				],
			],
		],
	],
	'view_manager'    => [
		'display_not_found_reason' => true,
		'display_exceptions'       => true,
		'doctype'                  => 'HTML5',
		'not_found_template'       => 'error/404',
		'exception_template'       => 'error/index',
		'template_map'             => [
			'layout/layout'             => __DIR__ . '/../view/layout/layout.phtml',
            'layout/login'              => __DIR__ . '/../view/layout/login.phtml',
            'layout/empty'              => __DIR__ . '/../view/layout/empty.phtml',
			'error/404'                 => __DIR__ . '/../view/error/404.phtml',
            'error/index'               => __DIR__ . '/../view/error/index.phtml',
            'error'                     => __DIR__ . '/../view/error/index.phtml',
		],
		'template_path_stack'      => [
            __DIR__ . '/../view',
		],
	],
];

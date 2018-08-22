<?php

return [
	'modules' => include __DIR__ . '/modules.config.php',
	'module_listener_options' => [
		'module_paths'      => [
			__DIR__.'/../module',
			__DIR__.'/../vendor',
		],
		'config_glob_paths' => [
			realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php',
		],
        'config_cache_key' => 'public.config.cache',
        'module_map_cache_key' => 'public.module.cache',

		// Whether or not to enable a configuration cache.
		// If enabled, the merged configuration will be cached and used in
		// subsequent requests.
		#'config_cache_enabled' => true,

		// Whether or not to enable a module class map cache.
		// If enabled, creates a module class map cache which will be used
		// by in future requests, to reduce the autoloading process.
		#'module_map_cache_enabled' => true,

		// The path in which to cache merged configuration.
		'cache_dir' => __DIR__.'/../data/cache/',
	],

	// Used to create an own service manager. May contain one or more child arrays.
	// 'service_listener_options' => [
	//     [
	//         'service_manager' => $stringServiceManagerName,
	//         'config_key'      => $stringConfigKey,
	//         'interface'       => $stringOptionalInterface,
	//         'method'          => $stringRequiredMethodName,
	//     ],
	// ],

	// Initial configuration with which to seed the ServiceManager.
	// Should be compatible with Zend\ServiceManager\Config.
];
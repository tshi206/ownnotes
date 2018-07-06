<?php

namespace OCA\OwnNotes\AppInfo;

$application = new Application();
$application->registerRoutes($this, [
	'resources' => [
		'note_api' => ['url' => '/api/0.1/notes']
	],
    'routes' => [
        [
            // The handler is the PageController's index method
            'name' => 'page#index',
            // The route
            'url' => '/',
            // Only accessible with GET requests
            'verb' => 'GET'
        ],
		['name' => 'note#index', 'url' => '/notes', 'verb' => 'GET'],
		['name' => 'note#show', 'url' => '/notes/{id}', 'verb' => 'GET'],
		['name' => 'note#create', 'url' => '/notes', 'verb' => 'POST'],
		['name' => 'note#update', 'url' => '/notes/{id}', 'verb' => 'PUT'],
		['name' => 'note#destroy', 'url' => '/notes/{id}', 'verb' => 'DELETE'],
		['name' => 'note_api#preflighted_cors', 'url' => '/api/0.1/{path}',
			'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']]
    ]
]);
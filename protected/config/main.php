<?php
return array(
	'language' => 'ru',
	'name' => 'FileMan',
	'import' => array('application.components.helpers.*', 'application.models.*'),
	'defaultController' => 'fileman',
	'components' => array(
		'urlManager' => array(
			'urlFormat' => 'path',
			'showScriptName' => false,
			'rules' => array(
				'<_a:\w+>.php' => 'fileman/<_a>'
			)
		)
	),	
	'params' => require 'params.php'
);
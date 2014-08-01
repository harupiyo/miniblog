<?php
session_start();
require_once 'util.php';
require_once 'template.php';

print template( 'template/manage_layout.tmpl',
	array(
		'flash'	=> get_flash(),
		'body'	=> template( 'template/manage_input.tmpl', make_input_env() ),
	)
);

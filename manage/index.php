<?php
session_start();
require_once 'util.php';
require_once 'template.php';

print template( 'template/manage_layout.tmpl',
	array(
		'menu'	=> '<p id="menu"><a href="edit.php">新規登録</a> <a href="../index.html" target="_blank">一般画面の確認</a></p>',
		'flash'	=> get_flash(),
		'body'	=> array_reduce(
			get_blogs(),
			function($carry, $blog){
				return $carry . template( 'template/manage_top_section.tmpl', array(
					'id'		=> get_id( $blog ),
					'date'		=> get_date( $blog ),
					'title'		=> get_title( $blog ),
					'summary'	=> get_summary( $blog ),
					'url'		=> get_filename( $blog ),
				) );
			}
		),
	)
);


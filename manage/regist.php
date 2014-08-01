<?php
session_start();
require_once 'util.php';
require_once 'template.php';

// 入力内容が妥当な場合
if( validate() ){
	
	// データベースに追加/更新
	$work = insert_or_update( $_POST );

	// 静的html のビルド
	build_static_html();
	
	// Flash メッセージの作成
	set_flash( "記事番号{$_POST['date']} の{$work}が完了しました" );
	
	// 管理画面トップに戻る
	header( 'Location: index.php' );
	
}
// エラーが有った場合は再入力画面を表示
else{
	
	print template( 'template/manage_layout.tmpl',
		array(
			'flash'	=> '<p class="error">エラーを修正して下さい</p>',
			'body'	=> template( 'template/manage_input.tmpl', make_input_env() ),
		)
	);
	
}


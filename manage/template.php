<?php
/*

変数だけをサポートしたシンプルなテンプレートエンジン

使い方
------

1. 予め変数を埋め込んだテンプレートファイルを用意しておく.

	例)
		template/sample.tmpl		// 拡張子はなんでもよい
			
			<!DOCTYPE HTML>
			<html lang="ja">
			<head>
				<meta charset="UTF-8">
				<title>{$env['title']}</title>
			</head>
			<body>
			<h1>{$env['message'][0]}</h1>
			</body>
			</html>

2. template() 関数を使う.

	例)
		print template( 'template/sample.tmpl', array( 'title' => 'miniblog', 'message' => array( 'hello', 'world' ) ) );

	第一引数にはテンプレートファイルへのパスを、 第二引数には、連想配列でデータを指定する.
	第二引数に指定した連想配列には$env という名前が割り当てられるので、テンプレート中で$env という変数として利用できる.
		テンプレートにはこの第二引数の$env 以外に、$_POST, $_GET 等のスーパーグローバル変数が使える
	
	返り値は文字列なので、print() やecho() で画面出力するか、file_put_contents() でファイルに書き出したりできる。

	複雑な例)
		print template( 'template/sample.tmpl',
			array(
				'title'	=> 'Mini Blog',
				'body'	=> template( 'template/manage_input.tmpl',
					array(
						'subtitle'	=> '今日も暑いですね'
					)
				),
			)
		);

		template() は文字列を返すだけだから、入れ子にするのも問題ない.
		これによってパーツを定義できる.

*/

// $filename の中の変数キーワードを、対応する変数の値に置換
// 存在しない変数名は'' に置き換える(エラーにはならないので注意)	// TODO 何らかの告知はしたい
function template( $filename, $env ){
	
	$str = file_get_contents( $filename );
	
	if( $str ){
		
		// {$....} の表現の中身、$... の部分だけを取得し、対応する変数に置換
		$str = preg_replace_callback(
			'/\{\$([^}]+)\}/u',
			function($matches) use($env) {
				$token = _tokenize( $matches[1] );
				$var = array_shift( $token );
				switch( count( $token ) ){
					case 0:
						// $$var の書き方は「可変変数」というPHP の機能
						// http://jp.php.net/manual/ja/language.variables.variable.php
						if( isset($$var ) ){
							return $$var;
						}
						return '';
					case 1:
						if( isset($$var)
							and isset( ${$var}[ $token[0] ] )
						){
							return ${$var}[ $token[0] ];
						}
						return '';
					case 2:
						if( isset($$var)
							and isset( ${$var}[ $token[0] ] )
							and isset( ${$var}[ $token[0] ][ $token[1] ] )
						){
							return ${$var}[ $token[0] ][ $token[1] ];
						}
						return '';
					case 3:
						if( isset($$var)
							and isset( ${$var}[ $token[0] ] )
							and isset( ${$var}[ $token[0] ][ $token[1] ] )
							and isset( ${$var}[ $token[0] ][ $token[1] ][ $token[2] ] )
						){
							return ${$var}[ $token[0] ][ $token[1] ][ $token[2] ];
						}
						return '';
					case 4:
						if( isset($$var)
							and isset( ${$var}[ $token[0] ] )
							and isset( ${$var}[ $token[0] ][ $token[1] ] )
							and isset( ${$var}[ $token[0] ][ $token[1] ][ $token[2] ] )
							and isset( ${$var}[ $token[0] ][ $token[1] ][ $token[2] ][ $token[3] ] )
						){
							return ${$var}[ $token[0] ][ $token[1] ][ $token[2] ][ $token[3] ];
						}
						return '';
					default:
						// TODO
						throw new Exception( 'template.php: 4次元以上の多次元配列は利用できません' );
				}
			} ,
			$str
		);
		
	}
	
	return $str;
}

// "env['TITLE'][1][0]" => array( 'env', 'TITLE', '1', '0' ) に分解
function _tokenize( $keyword ){
	return explode( ' ', str_replace( array( '[', ']', "'", '"' ), array( ' ', '', '', '' ), $keyword ) );
}


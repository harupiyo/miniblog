<?php
require_once 'config/config.php';

function today(){
	return date('Y-m-d');
}

//------------------------------------------------------------------------------
// 静的HTML 書き出し(ビルド) を実行
// 1. detail/*.html を全てファイル削除
// 2. detail/ の中に、データベースに登録されている記事を１ファイル毎に作成
// 3. index.html の作成

function build_static_html(){
	try{
		$dbh = get_dbh();
		$blogs = get_blogs( get_dbh() );	
		
		unlink_detail_pages();
		
		$archives = array_reduce(
			$blogs,
			function( $carry, $blog ){
				return $carry . template( 'template/build_archives.tmpl', array(
					'url'	=> get_filename( $blog ),
					'date'	=> get_date( $blog ),
				) );
			}
		);
		
		for( $i = 0 ; $i < count( $blogs ) ; $i++ ){
			$prev = isset( $blogs[$i - 1] ) ? $blogs[$i - 1] : null;
			$next = isset( $blogs[$i + 1] ) ? $blogs[$i + 1] : null;
			_build_detail( $blogs[$i], $prev, $next, $archives );
		}
		
		_build_index( $blogs, $archives );
		
	}catch(PDOException $e){
		print 'error:' . $e->getMessage();
	}
}

// 詳細記事を設置するフォルダの内容を全て削除する
// TODO 一瞬だけ見えない状況が起こるが、現状では仕様とする.
function unlink_detail_pages(){
	$files = glob( '../detail/*' );
	foreach( $files as $file ){
		unlink( $file );
	}
}

function _build_index( $data, $archives ){
	$contents = template( 'template/build_layout.tmpl', array(
		'archives' => $archives,
		'body' => array_reduce(
			$data,
			function( $carry, $blog ){
				return $carry . template( 'template/build_top_section.tmpl', array(
					'date'		=> get_date( $blog ),
					'title'		=> get_title( $blog ),
					'summary'	=> get_summary( $blog ),
					'url'		=> get_filename( $blog ),
				) );
			}
		)
	) );

	file_put_contents( '../index.html', $contents );
}

function _build_detail( $today, $prev, $next, $archives ){
	$contents = template( 'template/build_layout.tmpl', array(
		'basepath'	=> '../',
		'archives'	=> $archives,
		'body' => template( 'template/build_detail.tmpl', array(
			'date'		=> get_date( $today ),
			'title'		=> get_title( $today ),
			'body'		=> get_body( $today ),
			'prev'		=> get_link( $prev, '前の記事' ),
			'next'		=> get_link( $next, '次の記事' ),
		) )
	) );

	file_put_contents( '../detail/' . get_filename( $today ), $contents );
}

//------------------------------------------------------------------------------
// 入力画面の生成に必要な情報を生成
// 考慮するもの
//		$_POST が存在する場合は、前画面からの引き継ぎ情報を表示
//		$ERROR が存在する場合は、エラー情報を表示
function make_input_env(){

	// エラーで再入力画面を表示する時
	if( ! empty( $_POST ) ){
		global $ERROR;
		return array(
			'date'	=> $_POST['date'],
			'title'	=> $_POST['title'],
			'body'	=> $_POST['body'],
			'error' => $ERROR,
		);
	}
	
	// 更新画面を表示する時
	elseif( isset( $_GET['id'] ) ){
		$record = get_blog( $_GET['id'] );
		if( $record ){
			return array(
				'date'	=> get_id( $record ),
				'title'	=> get_title( $record ),
				'body'	=> get_body( $record ),
			);
		}
	}

	// 新規入力画面を表示する時 (更新時に存在しないデータid を指定した時も新規扱いとする)
	return array(
		'date'	=> today(),
	);

}

//------------------------------------------------------------------------------
// データ抽象レイヤ
// データベースから取得した情報そのまま扱うとは限らないので、加工処理を一手に引き受ける関数群

function id_to_date( $id ){
	$y = substr( $id, 0, 4 );
	$m = substr( $id, 4, 2 );
	$d = substr( $id, 6, 2 );
	return "{$y}-{$m}-{$d}";
}

function get_filename( $record ){
	return date_to_id( $record['id'] ) . '.html';
}

function get_id( $record ){
	return $record['id'];
}

function get_date( $record ){
	$ymd = split( '-', $record['id'] );
	return "{$ymd[0]}年{$ymd[1]}月{$ymd[2]}日";
}

function get_title( $record ){
	return $record['title'];
}

function get_body( $record ){
	return $record['body'];
}

function remove_tag( $html ){
	return preg_replace( '/<[^>]+>/u', '', $html );
}

function get_summary( $record ){
	$contents = remove_tag( get_body( $record ) );
	if( mb_strlen( $contents ) > 80 ){
		$contents = mb_substr( $contents, 0, 80, 'UTF-8' ) . '...';
	}
	return $contents;
}

function get_link( $record, $message ){
	if( $record !== null ){
		$url = get_filename( $record );
		return "<li><a href='{$url}'>{$message}</a></li>";
	}
}

function date_to_id( $ymd ){
	return str_replace('-', '', $ymd);
}

//------------------------------------------------------------------------------
// データベースアクセス

// この関数を利用するコードはtry-catch で例外から保護すること
function get_dbh(){
	static $dbh = null;
	global $ENV;
	if( $dbh === null ){
		$dbh = new PDO( 'mysql:host=localhost;dbname=miniblog', $ENV['database']['ID'], $ENV['database']['PASSWORD'] );
	}
	return $dbh;
}

function insert_or_update( $data ){
	try{
		$dbh = get_dbh();
		
		// すでに同じ日付の記事があるかを調べる
		$stmt = $dbh->prepare('SELECT * FROM miniblog WHERE id = :id');
		$stmt->execute( array(
			'id'	=> $_POST['date'],
		) );
		$record = $stmt->fetch( PDO::FETCH_ASSOC );

		$work = '';
		if( $record ){
			$stmt = $dbh->prepare('UPDATE miniblog SET title=:title, body=:body, modified=NOW() WHERE id=:id');
			$work = '更新';
		}else{
			$stmt = $dbh->prepare('INSERT INTO miniblog (id,title,body,created,modified) VALUES(:id,:title,:body,NOW(),NOW())');
			$work = '新規登録';
		}
		
		$stmt->execute( array(
			'id'	=> $_POST['date'],
			'title'	=> $_POST['title'],
			'body'	=> $_POST['body'],
		) );

		return $work;
		
	}catch(PDOException $e){
		print 'error:' . $e->getMessage();
	}
	
}

// 全ての記事を最新順(=日付の降順) で取得
function get_blogs(){
	try{
		$dbh = get_dbh();
		$stmt = $dbh->prepare('SELECT * FROM miniblog ORDER BY id DESC');
		$stmt->execute();
		return $stmt->fetchAll();
		
	}catch(PDOException $e){
		print 'error:' . $e->getMessage();
	}
	
}

function get_blog( $id ){
	try{
		$dbh = get_dbh();
		$stmt = $dbh->prepare('SELECT * FROM miniblog WHERE id=:id');
		$stmt->execute( array( 'id' => $id ) );
		return $stmt->fetch();
		
	}catch(PDOException $e){
		print 'error:' . $e->getMessage();
	}
}

function delete_blog( $id ){
	try{
		$dbh = get_dbh();
		$stmt = $dbh->prepare('DELETE FROM miniblog WHERE id=:id');
		$stmt->execute( array( 'id' => $id ) );
		
	}catch(PDOException $e){
		print 'error:' . $e->getMessage();
	}
}

//------------------------------------------------------------------------------
// フラッシュメッセージ
// 画面に一度だけ表示されるメッセージの仕組みを提供

function set_flash($message){
	$_SESSION['flash'] = $message;
}

function get_flash(){
	$message = $_SESSION['flash'];
	$_SESSION['flash'] = '';
	return $message;
}

//------------------------------------------------------------------------------
// バリデーション

$ERROR = array();

function validate(){
	global $ERROR;
	// 必須チェック
	if( ! isset( $_POST['date'] ) or $_POST['date'] === '' ){
		$ERROR['date'] = '必須です';
	}
	if( ! isset( $_POST['title'] ) or $_POST['title'] === '' ){
		$ERROR['title'] = '必須です';
	}
	if( ! isset( $_POST['body'] ) or $_POST['body'] === '' ){
		$ERROR['body'] = '必須です';
	}
	return count($ERROR) === 0 ? true : false;
}

function get_error( $key ){
	global $ERROR;
	if( isset( $ERROR[ $key ] ) ){
		return "<span class='error'>{$ERROR[$key]}</span>";
	}
	return '';
}


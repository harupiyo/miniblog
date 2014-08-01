<?php
session_start();
require_once 'util.php';

delete_blog( $_GET['id'] );

set_flash( "記事番号{$_GET['id']} の削除が完了しました" );

header('Location: index.php');

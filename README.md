miniblog
========

PHP で書かれた"軽薄" なBlog システムです。

コンセプト
----------

PHP 言語の初学者向けの教材として制作しました。

PHP 初学者がいくつかの単純なWeb アプリを作ったあとで「もう少し本格的なWeb アプリを作ってみたい」という時に参考になる企画/ソースコードになるように配慮しました。

ここで想定している"PHP 初学者" とは、以下の勉強を成した人を指します。
 * 変数、データ型(文字列型,整数型,配列/連想配列型)、演算子、制御構文(if,for)、関数(function) の最重要基本パーツ
 * Web アプリに必要な$_GET, $_POST, $_COOKIE, $_SESSION
 * MySQL で、SQL の基本であるINSERT,UPDATE,DELETE,SELECT の４つと、それをPHP から利用するPDO クラスの簡単な使い方

ゆっくりでもいいので、これらを組み合わせて考えることができるレベルです。
このあたりの人がターゲットです。

オススメの勉強方法を書いておきます。
1. 動作させてみてください。
2. アプリを使いながら「どういう仕組で動いているのか」を想像してみてください。
3. ソースコードを一切見ずに、自分で同じ仕組みを作ってみてください。(できるところまでで結構です)
4. 詰まったら、このソースコードを研究してみてください。ソースコードを理解する近道は、処理の流れ、データの流れを追うことです。データを見える化する var_dump() を適宜の場所に挿入して、データの流れを追うやり方がおススメです。

プログラムの書き方はひと通りではありません。よって、3. の中で、いかに自分なりのやりかたを考えるか、が大事だと思って下さい。
このソースコードが大事なのではありません。

Blog システムの内容
-------------------

記事の登録、更新、削除ができるだけです。html は管理画面から記事を更新したタイミングで静的書き出しを行っています。
コンセプトに従い、ほとんど最低限の機能のみに絞っています。
これを土台に、自分なりに機能を自由に追加をして遊んでいただきたいと思います。

インストール
------------

1. ダウンロード、展開、ドキュメントルート下に設置を行って下さい。

index.html と detail フォルダには書き込み権限を与えておく必要があります。

bash> chmod 777 index.html
bash> chmod 777 detail

2. MySQL にminiblog データベースを作成し、miniblog テーブルを作成します。
mysql> CREATE DATABASE miniblog CHARACTER SET UTF8;
mysql> use miniblog;
mysql> CREATE TABLE myblog (
	id			DATE PRIMARY KEY,
	title		text NOT NULL,
	body		text NOT NULL,
	created		DATETIME NOT NULL,
	modified	DATETIME NOT NULL
) Engine=InnoDB;
mysql> exit

3. 設定ファイルの編集
manage/config/config.php.sample をリネームし、manage/config/config.php として下さい。
それから中に書いてあるコメントを見ながら設定を行って下さい。

4. 動作確認
まずはじめに管理画面の動作を確認しましょう。
ドキュメントルート下にminiblog というフォルダ名でインストールした場合のURL は次のようなものでしょう。

	管理画面
		http://localhost/miniblog/manage/index.php

ID/パスワードによるアクセス制限はかけてありませんので、すぐ管理画面が確認できるはずです。
管理画面から何か一つ記事を作成して下さい。
そうするとトップページが作成されます。
トップページ
	http://localhost/miniblog/index.html

トップページが確認できたら、作業終了です。

デザインについて
----------------
管理画面はデザインには全く力を入れていません。

一般画面は、Bootstrap のサンプル http://getbootstrap.com/examples/blog/ を使用させていただきました。

	Bootstrap v3.2.0 (http://getbootstrap.com)
	Copyright 2011-2014 Twitter, Inc.
	Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)

favicon はこちらのものを利用しています。
	http://nonty.net/item/favicon/

credit
------
Copyright (c) 2014 harupiyo

ライセンス(使用許可)
--------------------
GPLv3 に基づき自由に改変、配布してかまいません。
詳しくはLICENSE ファイルをご覧ください。


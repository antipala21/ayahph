<?php
$file = '../../config/my.php';
if(is_file($file)){
	require_once($file);
}

$config['my']['meta'] = array(
	'home' => array(
		'title' => 'オンライン英会話ならskype（スカイプ）不要のネイティブキャンプ',
		'description' => 'スカイプ不要・オンラインで英会話レッスンを受けることが出来るネイティブキャンプ！普段利用しているブラウザで英会話を学べます！手軽さナンバーワン！',
	),
	'mypage' => array(
		'title' => 'マイページ｜オンライン英会話ならskype（スカイプ）不要のネイティブキャンプ',
		'description' => '',
	),
	'download_browser' => array(
		'title' => '対応ブラウザ手順｜オンライン英会話のネイティブキャンプ',
		'description' => '',
	),
	'first_step' => array(
		'title' => '初めての方 | オンライン英会話のネイティブキャンプ',
		'description' => 'ネイティブキャンプでのレッスンの受け方などを解説しています。簡単にレッスンをお受け頂ける環境を用意しております。',
	),
	'price' => array(
		'title' => '料金について｜オンライン英会話のネイティブキャンプ',
		'description' => 'ネイティブキャンプのレッスン費用について説明致します。各種クレジットカードもご利用頂けます。',
	),
	'tos' => array(
		'title' => '利用規約 | オンライン英会話のネイティブキャンプ',
		'description' => 'ネイティブキャンプの利用規約です。当サイトで英会話のレッスンをお受け頂く際にはご一読下さいますようお願い致します。',
	),
	'law' => array(
		'title' => '特定商取引法 | オンライン英会話のネイティブキャンプ',
		'description' => '特定商取引法に基づく表記です。ネイティブキャンプはVJソリューションズ株式会社が運営しております。',
	),
	'privacy' => array(
		'title' => '個人情報取り扱い | オンライン英会話のネイティブキャンプ',
		'description' => 'ネイティブキャンプの個人情報の取り扱いについてです。個人情報を厳重に管理し漏洩などがないように配慮しております。',
	),
	'company' => array(
		'title' => '会社概要 | オンライン英会話のネイティブキャンプ',
		'description' => 'ネイティブキャンプの運営会社概要です。VJソリューションズ株式会社がネイティブキャンプを運営しております。',
	),
	'login' => array(
		'title' => 'ネイティブキャンプログイン | オンライン英会話のネイティブキャンプ',
		'description' => 'ネイティブキャンプのログインフォームです。会員の方はログインフォームからログインしてからご利用下さい。',
	),
	'register-index' => array(
		'title' => '無料会員登録 | オンライン英会話のネイティブキャンプ',
		'description' => 'ネイティブキャンプの新規登録フォームです。無料でご登録頂けます。レッスンをご希望の方は新規登録を宜しくお願い致します。',
	),
	'register-confirm_email' => array(
		'title' => '認証メール送信完了 | オンライン英会話のネイティブキャンプ',
	),
	'register-tellno_input' => array(
		'title' => 'SMS認証 | オンライン英会話のネイティブキャンプ',
	),
	'register-profile_input' => array(
		'title' => 'ユーザー情報入力 | オンライン英会話のネイティブキャンプ'
	),
	'register-complete' => array(
		'title' => '登録完了 | オンライン英会話のネイティブキャンプ'
	),
	'appointment' => array(
		'title' => '予約・講師検索詳細 | オンライン英会話のネイティブキャンプ',
		'description' => '英会話のレッスン予約を行います。日時を選択入力して頂くだけで簡単にご予約頂けます。いつでも待機している講師がいますので、今すぐレッスンも可能です。',
	),
	'account-index' => array(
		'title' => 'ユーザー設定 | オンライン英会話のネイティブキャンプ'
	),
	'account-device' => array(
		'title' => 'サウンド設定 | オンライン英会話のネイティブキャンプ'
	),
	'account-changeemail' => array(
		'title' => 'メールアドレスを変更 | オンライン英会話のネイティブキャンプ'
	),
	'account-activate' => array(
		'title' => 'メールアドレス変更完了 | オンライン英会話のネイティブキャンプ'
	),
	'account-changepass' => array(
		'title' => 'パスワードを変更 | オンライン英会話のネイティブキャンプ'
	),
	'account-changepasscomplete' => array(
		'title' => 'パスワード変更完了 | オンライン英会話のネイティブキャンプ'
	),
	'accountdeactivation-index' => array(
		'title' => '退会手続き | オンライン英会話のネイティブキャンプ'
	),
	'accountdeactivation-confirm' => array(
		'title' => '退会手続き確認 | オンライン英会話のネイティブキャンプ'
	),
	'accountdeactivation-complete' => array(
		'title' => '退会完了 | オンライン英会話のネイティブキャンプ'
	),
	'forgot-index' => array(
		'title' => 'パスワードを忘れた場合 | オンライン英会話のネイティブキャンプ',
		'description' => 'パスワードを忘れた時のパスワードの再送フォームです。登録したメールアドレスをご入力頂き送信して下さい。',
	),
	'forgot-sent' => array(
		'title' => 'パスワードを再送 | オンライン英会話のネイティブキャンプ'
	),
	'forgot-activate' => array(
		'title' => '新しいパスワードの設定 | オンライン英会話のネイティブキャンプ'
	),
	'forgot-complete' => array(
		'title' => 'パスワードの変更完了 | オンライン英会話のネイティブキャンプ'
	),
	'waiting-index' => array(
		'title' => '講師一覧 | オンライン英会話のネイティブキャンプ'
	),
	'announce-index' => array(
		'title' => 'お知らせ一覧 | オンライン英会話のネイティブキャンプ'
	),
#	'announce-detail' => array(
#		'title' => 'お知らせ詳細 | オンライン英会話のネイティブキャンプ'
#	),
	'cs-index' => array(
		'title' => 'お問い合わせ | オンライン英会話のネイティブキャンプ',
		'description' => 'ネイティブキャンプへのお問い合わせです。ご不明な点についてはお問い合わせ下さい。',
	),
	'cs-confirm_inquiry' => array(
		'title' => 'お問い合わせ確認 | オンライン英会話のネイティブキャンプ',
	),
	'cs-confirm_reply' => array(
		'title' => 'お問い合わせ確認 | オンライン英会話のネイティブキャンプ',
	),
	'cs-complete' => array(
		'title' => 'お問い合わせ完了 | オンライン英会話のネイティブキャンプ',
	),
	'faq-index' => array(
		'title' => 'FAQ | オンライン英会話のネイティブキャンプ',
		'description' => 'よくある質問も掲載しておりますので、ご不明な点についてはよくある質問もご覧下さい。',
	),
	'lessonhistory' => array(
		'title' => 'レッスン履歴 | オンライン英会話のネイティブキャンプ',
	),
	'lessonhistory-detail' => array(
		'title' => 'レッスン履歴 | オンライン英会話のネイティブキャンプ',
	),
	'lessonmessage' => array(
		'title' => 'メッセージ | オンライン英会話のネイティブキャンプ',
	),
	'lessonreservation' => array(
		'title' => '予約リスト | オンライン英会話のネイティブキャンプ',
	),
	'points' => array(
		'title' => 'コイン購入 | オンライン英会話のネイティブキャンプ',
		'description' => '',
	),
	'textbook' => array(
		'title' => '教材 | オンライン英会話のネイティブキャンプ'
	),
	'vocabulary' => array(
		'title' => '単語一覧 | オンライン英会話のネイティブキャンプ'
	),
	'favorite' => array(
		'title' => 'お気に入り | オンライン英会話のネイティブキャンプ'
	),
	'memo' => array(
		'title' => 'メモ | オンライン英会話のネイティブキャンプ'
	),
	'ranking' => array(
		'title' => 'ランキング｜ オンライン英会話のネイティブキャンプ'
	)
);
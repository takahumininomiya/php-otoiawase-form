


	// 件名を設定
	$auto_reply_subject = 'お問い合わせありがとうございます。';

	// 本文を設定
	$auto_reply_text = "この度は、お問い合わせ頂き誠にありがとうございます。
下記の内容でお問い合わせを受け付けました。\n\n";
	$auto_reply_text .= "お問い合わせ日時：" . date("Y-m-d H:i") . "\n";
	$auto_reply_text .= "氏名：" . $_POST['your_name'] . "\n";
	$auto_reply_text .= "メールアドレス：" . $_POST['email'] . "\n";

	if( $_POST['gender'] === "male" ) {
		$auto_reply_text .= "性別：男性\n";
	} else {
		$auto_reply_text .= "性別：女性\n";
	}
	
	if( $_POST['age'] === "1" ){
		$auto_reply_text .= "年齢：〜19歳\n";
	} elseif ( $_POST['age'] === "2" ){
		$auto_reply_text .= "年齢：20歳〜29歳\n";
	} elseif ( $_POST['age'] === "3" ){
		$auto_reply_text .= "年齢：30歳〜39歳\n";
	} elseif ( $_POST['age'] === "4" ){
		$auto_reply_text .= "年齢：40歳〜49歳\n";
	} elseif( $_POST['age'] === "5" ){
		$auto_reply_text .= "年齢：50歳〜59歳\n";
	} elseif( $_POST['age'] === "6" ){
		$auto_reply_text .= "年齢：60歳〜\n";
	}

	$auto_reply_text .= "お問い合わせ内容：" . nl2br($_POST['contact']) . "\n\n";
	$auto_reply_text .= "GRAYCODE 事務局";
	// テキストメッセージをセット
	$body = "--__BOUNDARY__\n";
	$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
	$body .= $auto_reply_text . "\n";
	$body .= "--__BOUNDARY__\n";

	// ファイルを添付
	if( !empty($clean['attachment_file']) ) {
		$body .= "Content-Type: application/octet-stream; name=\"{$clean['attachment_file']}\"\n";
		$body .= "Content-Disposition: attachment; filename=\"{$clean['attachment_file']}\"\n";
		$body .= "Content-Transfer-Encoding: base64\n";
		$body .= "\n";
		$body .= chunk_split(base64_encode(file_get_contents(FILE_DIR.$clean['attachment_file'])));
		$body .= "--__BOUNDARY__\n";
	}
	// 自動返信メール送信
	mb_send_mail( $_POST['email'], $auto_reply_subject,$body, $auto_reply_text, $header);
	// 運営側へ送るメールの件名
	$admin_reply_subject = "お問い合わせを受け付けました";
	
	// 本文を設定
	$admin_reply_text = "下記の内容でお問い合わせがありました。\n\n";
	$admin_reply_text .= "お問い合わせ日時：" . date("Y-m-d H:i") . "\n";
	$admin_reply_text .= "氏名：" . $_POST['your_name'] . "\n";
	$admin_reply_text .= "メールアドレス：" . $_POST['email'] . "\n";

	if( $_POST['gender'] === "male" ) {
		$admin_reply_text .= "性別：男性\n";
	} else {
		$admin_reply_text .= "性別：女性\n";
	}
	
	if( $_POST['age'] === "1" ){
		$admin_reply_text .= "年齢：〜19歳\n";
	} elseif ( $_POST['age'] === "2" ){
		$admin_reply_text .= "年齢：20歳〜29歳\n";
	} elseif ( $_POST['age'] === "3" ){
		$admin_reply_text .= "年齢：30歳〜39歳\n";
	} elseif ( $_POST['age'] === "4" ){
		$admin_reply_text .= "年齢：40歳〜49歳\n";
	} elseif( $_POST['age'] === "5" ){
		$admin_reply_text .= "年齢：50歳〜59歳\n";
	} elseif( $_POST['age'] === "6" ){
		$admin_reply_text .= "年齢：60歳〜\n";
	}

	$admin_reply_text .= "お問い合わせ内容：" . nl2br($_POST['contact']) . "\n\n";
	
	// テキストメッセージをセット
$body = "--__BOUNDARY__\n";
$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
$body .= $admin_reply_text . "\n";
$body .= "--__BOUNDARY__\n";

// ファイルを添付
if( !empty($clean['attachment_file']) ) {
	$body .= "Content-Type: application/octet-stream; name=\"{$clean['attachment_file']}\"\n";
	$body .= "Content-Disposition: attachment; filename=\"{$clean['attachment_file']}\"\n";
	$body .= "Content-Transfer-Encoding: base64\n";
	$body .= "\n";
	$body .= chunk_split(base64_encode(file_get_contents(FILE_DIR.$clean['attachment_file'])));
	$body .= "--__BOUNDARY__\n";
}

	// 管理者へメール送信
	mb_send_mail( 'webmaster@gray-code.com', $admin_reply_subject, $admin_reply_text, $header);
} else {
	$page_flag = 0;
}
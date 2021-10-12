<?php
define( "FILE_DIR", "messege1.txt");
// 書き込み日時を取得
$current_date = date("Y-m-d H:i:s");
// 変数の初期化
$page_flag = 0;
$clean = array();
$error = array();
$pdo = null;
$stmt = null;
$res = null;
$option = null;

// データベースに接続
try{
	$option = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
	);
$pdo = new PDO('mysql:charset=UTF8;dbname=otoiawase;host=localhost', 'root', 'root',$option);
} catch(PDOException $e) {

    // 接続エラーのときエラー内容を取得する
    $error_message[] = $e->getMessage();
}
// サニタイズ
if( !empty($_POST) ) {

	foreach( $_POST as $key => $value ) {
		$clean[$key] = htmlspecialchars( $value, ENT_QUOTES);
	} 
}

if( !empty($clean['btn_confirm']) ) {

	$error = validation($clean);

	// ファイルのアップロード
	if( !empty($_FILES['attachment_file']['tmp_name']) ) {

		$upload_res = move_uploaded_file( $_FILES['attachment_file']['tmp_name'], FILE_DIR.$_FILES['attachment_file']['name']);

		if( $upload_res !== true ) {
			$error[] = 'ファイルのアップロードに失敗しました。';
		} else {
			$clean['attachment_file'] = $_FILES['attachment_file']['name'];
		}
	}

	if( empty($error) ) {

		$page_flag = 1;

		// セッションの書き込み
		session_start();
		$_SESSION['page'] = true;		
	}

} elseif( !empty($clean['btn_submit']) ) {

	session_start();
	if( !empty($_SESSION['page']) && $_SESSION['page'] === true ) {

		// セッションの削除
		unset($_SESSION['page']);

		$page_flag = 2;

		// 変数とタイムゾーンを初期化
		$header = null;
		$body = null;
		$admin_body = null;
		$auto_reply_subject = null;
		$auto_reply_text = null;
		$admin_reply_subject = null;
		$admin_reply_text = null;

		date_default_timezone_set('Asia/Tokyo');

		//日本語の使用宣言
		mb_language("ja");
		mb_internal_encoding("UTF-8");
	
		$header = "MIME-Version: 1.0\n";
		$header = "Content-Type: multipart/mixed;boundary=\"__BOUNDARY__\"\n";
		$header .= "From: GRAYCODE <noreply@gray-code.com>\n";
		$header .= "Reply-To: GRAYCODE <noreply@gray-code.com>\n";
	
		// 件名を設定
		$auto_reply_subject = 'お問い合わせありがとうございます。';
	
		// 本文を設定
		$auto_reply_text = "この度は、お問い合わせ頂き誠にありがとうございます。
	下記の内容でお問い合わせを受け付けました。\n\n";
		$auto_reply_text .= "お問い合わせ日時：" . date("Y-m-d H:i") . "\n";
		$auto_reply_text .= "氏名：" . $clean['your_name'] . "\n";
		$auto_reply_text .= "メールアドレス：" . $clean['email'] . "\n";
	
		if( $clean['gender'] === "male" ) {
			$auto_reply_text .= "性別：男性\n";
		} else {
			$auto_reply_text .= "性別：女性\n";
		}
		
		if( $clean['age'] === "1" ){
			$auto_reply_text .= "年齢：〜19歳\n";
		} elseif ( $clean['age'] === "2" ){
			$auto_reply_text .= "年齢：20歳〜29歳\n";
		} elseif ( $clean['age'] === "3" ){
			$auto_reply_text .= "年齢：30歳〜39歳\n";
		} elseif ( $clean['age'] === "4" ){
			$auto_reply_text .= "年齢：40歳〜49歳\n";
		} elseif( $clean['age'] === "5" ){
			$auto_reply_text .= "年齢：50歳〜59歳\n";
		} elseif( $clean['age'] === "6" ){
			$auto_reply_text .= "年齢：60歳〜\n";
		}
	
		$auto_reply_text .= "お問い合わせ内容：" . nl2br($clean['contact']) . "\n\n";
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
		mb_send_mail( $clean['email'], $auto_reply_subject, $body, $header);
	
		// 運営側へ送るメールの件名
		$admin_reply_subject = "お問い合わせを受け付けました";
		// 本文を設定
		$admin_reply_text = "下記の内容でお問い合わせがありました。\n\n";
		$admin_reply_text .= "お問い合わせ日時：" . date("Y-m-d H:i") . "\n";
		$admin_reply_text .= "氏名：" . $clean['your_name'] . "\n";
		$admin_reply_text .= "メールアドレス：" . $clean['email'] . "\n";
	
		if( $clean['gender'] === "male" ) {
			$admin_reply_text .= "性別：男性\n";
		} else {
			$admin_reply_text .= "性別：女性\n";
		}
	
		if( $clean['age'] === "1" ){
			$admin_reply_text .= "年齢：〜19歳\n";
		} elseif ( $clean['age'] === "2" ){
			$admin_reply_text .= "年齢：20歳〜29歳\n";
		} elseif ( $clean['age'] === "3" ){
			$admin_reply_text .= "年齢：30歳〜39歳\n";
		} elseif ( $clean['age'] === "4" ){
			$admin_reply_text .= "年齢：40歳〜49歳\n";
		} elseif( $clean['age'] === "5" ){
			$admin_reply_text .= "年齢：50歳〜59歳\n";
		} elseif( $clean['age'] === "6" ){
			$admin_reply_text .= "年齢：60歳〜\n";
		}
	
		$admin_reply_text .= "お問い合わせ内容：" . nl2br($clean['contact']) . "\n\n";
		
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
		mb_send_mail( 'webmaster@gray-code.com', $admin_reply_subject, $body, $header);
		
	} else {
		$page_flag = 0;
	}	
}

function validation($data) {

	$error = array();

	// エラーが発生した時はロールバック
	$pdo->rollBack();


	// 氏名のバリデーション
	if( empty($data['your_name']) ) {
		$error[] = "「氏名」は必ず入力してください。";

	} elseif( 20 < mb_strlen($data['your_name']) ) {
		$error[] = "「氏名」は20文字以内で入力してください。";
	}

	// メールアドレスのバリデーション
	if( empty($data['email']) ) {
		$error[] = "「メールアドレス」は必ず入力してください。";

	} elseif( !preg_match( '/^[0-9a-z_.\/?-]+@([0-9a-z-]+\.)+[0-9a-z-]+$/', $data['email']) ) {
		$error[] = "「メールアドレス」は正しい形式で入力してください。";
	}

	// 性別のバリデーション
	if( empty($data['gender']) ) {
		$error[] = "「性別」は必ず入力してください。";

	} elseif( $data['gender'] !== 'male' && $data['gender'] !== 'female' ) {
		$error[] = "「性別」は必ず入力してください。";
	}

	// 年齢のバリデーション
	if( empty($data['age']) ) {
		$error[] = "「年齢」は必ず入力してください。";

	} elseif( (int)$data['age'] < 1 || 6 < (int)$data['age'] ) {
		$error[] = "「年齢」は必ず入力してください。";
	}

	// お問い合わせ内容のバリデーション
	if( empty($data['contact']) ) {
		$error[] = "「お問い合わせ内容」は必ず入力してください。";
	}

	// プライバシーポリシー同意のバリデーション
	if( empty($data['agreement']) ) {
		$error[] = "プライバシーポリシーをご確認ください。";

	} elseif( (int)$data['agreement'] !== 1 ) {
		$error[] = "プライバシーポリシーをご確認ください。";
	}

	return $error;
	// プリペアドステートメントを削除
	$stmt = null;
}
if( empty($error_message) ) {

	// メッセージのデータを取得する
	$sql = "SELECT name,message,gender,mailadress,age FROM message ORDER BY name DESC";
	$message_array = $pdo->query($sql);
}
// メッセージを保存するファイルのパス設定
define( 'FILENAME', './message1.txt');
// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');
// 変数の初期化
$current_date = null;
$data = null;
$file_handle = null;


$pdo->beginTransaction();
	try{
	$stmt = $pdo->prepare("INSERT INTO message (name, message, gender,mailaddress,age) 
		VALUES ( :name, :message, :gender, :mailaddress, :age,)");
		// 値をセット
		$stmt->bindParam( ':name', $clean['name'], PDO::PARAM_STR);
		$stmt->bindParam( ':message', $clean['message'], PDO::PARAM_STR);
		$stmt->bindParam( ':gender', $gender, PDO::PARAM_STR);
		$stmt->bindParam( ':mailaddress', $mailaddress, PDO::PARAM_STR);
		$stmt->bindParam( ':age', $age, PDO::PARAM_STR);
		// SQLクエリの実行
		$res = $stmt->execute();
	// コミット
	$res = $pdo->commit();
	}
	catch(Exception $e) {
if( !empty($_POST['btn_submit']) ) {
	if( $file_handle = fopen( FILENAME, "a") ) {
		// 書き込み日時を取得
		$current_date = date("Y-m-d H:i:s");
	
		// 書き込むデータを作成
		$data = "'".$_POST['view_name']."','".$_POST['message']."','".$_POST['gender']."','".$_POST['mailaddress']."','"/$_POST['age']."'\n";
		// 書き込み
		fwrite( $file_handle, $data);
		/// データベースの接続を閉じる
$pdo = null;
}
// ファイルを閉じる
		fclose( $file_handle);
	}
}	
?>

<!DOCTYPE>
<html lang="ja">
<head>
	<link rel = "stylesheet" href="index1.css">
<title>お問い合わせフォーム</title>
</head>
<body>
<h1>お問い合わせフォーム</h1>
<?php if( $page_flag === 1 ): ?>

<form method="post" action="">
	<div class="element_wrap">
		<label>氏名</label>
		<p><?php echo $clean['your_name']; ?></p>
	</div>
	<div class="element_wrap">
		<label>メールアドレス</label>
		<p><?php echo $clean['email']; ?></p>
	</div>
	<div class="element_wrap">
		<label>性別</label>
		<p><?php if( $clean['gender'] === "male" ){ echo '男性'; }else{ echo '女性'; } ?></p>
	</div>
	<div class="element_wrap">
		<label>年齢</label>
		<p><?php if( $clean['age'] === "1" ){ echo '〜19歳'; }
		elseif( $clean['age'] === "2" ){ echo '20歳〜29歳'; }
		elseif( $clean['age'] === "3" ){ echo '30歳〜39歳'; }
		elseif( $clean['age'] === "4" ){ echo '40歳〜49歳'; }
		elseif( $clean['age'] === "5" ){ echo '50歳〜59歳'; }
		elseif( $clean['age'] === "6" ){ echo '60歳〜'; } ?></p>
	</div>
	<div class="element_wrap">
		<label>お問い合わせ内容</label>
		<p><?php echo nl2br($clean['contact']); ?></p>
	</div>
	<?php if( !empty($clean['attachment_file']) ): ?>
	<div class="element_wrap">
		<label>画像ファイルの添付</label>
		<p><img src="<?php echo FILE_DIR.$clean['attachment_file']; ?>"></p>
	</div>
	<?php endif; ?>
	<div class="element_wrap">
		<label>プライバシーポリシーに同意する</label>
		<p><?php if( $clean['agreement'] === "1" ){ echo '同意する'; }else{ echo '同意しない'; } ?></p>
	</div>
	<input type="submit" name="btn_back" value="戻る">
	<input type="submit" name="btn_submit" value="送信">
	<input type="hidden" name="your_name" value="<?php echo $clean['your_name']; ?>">
	<input type="hidden" name="email" value="<?php echo $clean['email']; ?>">
	<input type="hidden" name="gender" value="<?php echo $clean['gender']; ?>">
	<input type="hidden" name="age" value="<?php echo $clean['age']; ?>">
	<input type="hidden" name="contact" value="<?php echo $clean['contact']; ?>">
	<?php if( !empty($clean['attachment_file']) ): ?>
		<input type="hidden" name="attachment_file" value="<?php echo $clean['attachment_file']; ?>">
	<?php endif; ?>
	<input type="hidden" name="agreement" value="<?php echo $clean['agreement']; ?>">
</form>

<?php elseif( $page_flag === 2 ): ?>

<p>送信が完了しました。</p>

<?php else: ?>

<?php if( !empty($error) ): ?>
	<ul class="error_list">
	<?php foreach( $error as $value ): ?>
		<li><?php echo $value; ?></li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>

<form method="post" action="" enctype="multipart/form-data">
	<div class="element_wrap">
		<label>氏名</label>
		<input type="text" name="your_name" value="<?php if( !empty($clean['your_name']) ){ echo $clean['your_name']; } ?>">
	</div>
	<div class="element_wrap">
		<label>メールアドレス</label>
		<input type="text" name="email" value="<?php if( !empty($clean['email']) ){ echo $clean['email']; } ?>">
	</div>
	<div class="element_wrap">
		<label>性別</label>
		<label for="gender_male"><input id="gender_male" type="radio" name="gender" value="male" <?php if( !empty($clean['gender']) && $clean['gender'] === "male" ){ echo 'checked'; } ?>>男性</label>
		<label for="gender_female"><input id="gender_female" type="radio" name="gender" value="female" <?php if( !empty($clean['gender']) && $clean['gender'] === "female" ){ echo 'checked'; } ?>>女性</label>
	</div>
	<div class="element_wrap">
		<label>年齢</label>
		<select name="age">
			<option value="">選択してください</option>
			<option value="1" <?php if( !empty($clean['age']) && $clean['age'] === "1" ){ echo 'selected'; } ?>>〜19歳</option>
			<option value="2" <?php if( !empty($clean['age']) && $clean['age'] === "2" ){ echo 'selected'; } ?>>20歳〜29歳</option>
			<option value="3" <?php if( !empty($clean['age']) && $clean['age'] === "3" ){ echo 'selected'; } ?>>30歳〜39歳</option>
			<option value="4" <?php if( !empty($clean['age']) && $clean['age'] === "4" ){ echo 'selected'; } ?>>40歳〜49歳</option>
			<option value="5" <?php if( !empty($clean['age']) && $clean['age'] === "5" ){ echo 'selected'; } ?>>50歳〜59歳</option>
			<option value="6" <?php if( !empty($clean['age']) && $clean['age'] === "6" ){ echo 'selected'; } ?>>60歳〜</option>
		</select>
	</div>
	<div class="element_wrap">
		<label>お問い合わせ内容</label>
		<textarea name="contact"><?php if( !empty($clean['contact']) ){ echo $clean['contact']; } ?></textarea>
	</div>
	<div class="element_wrap">
		<label>画像ファイルの添付</label>
		<input type="file" name="attachment_file">
	</div>
	<div class="element_wrap">
		<label for="agreement"><input id="agreement" type="checkbox" name="agreement" value="1" <?php if( !empty($clean['agreement']) && $clean['agreement'] === "1" ){ echo 'checked'; } ?>>プライバシーポリシーに同意する</label>
	</div>
	<input type="submit" name="btn_confirm" value="入力内容を確認する">
</form>

<?php endif; ?>
</body>
</html>
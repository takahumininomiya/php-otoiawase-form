
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

define( FILE_DIR, "/images/test/");

// 設置した場所のパスを指定する
require('./PHPMailer/src/PHPMailer.php');
require('./PHPMailer/src/Exception.php');
require('./PHPMailer/src/SMTP.php');

// 変数の初期化
$page_flag = 0;
$clean = array();
$error = array();


// サニタイズ
if( !empty($_POST) ) 
{
	foreach( $_POST as $key => $value ) {
		$clean[$key] = htmlspecialchars( $value, ENT_QUOTES);
	}
}

if( !empty($_POST['btn_confirm']) )
{
	$error = validation($clean);
	// ファイルのアップロード
	if( !empty($_FILES['attachment_file']['tmp_name']) ) 
	{
		$upload_res = move_uploaded_file( $_FILES['attachment_file']['tmp_name'], FILE_DIR.$_FILES['attachment_file']['name']);
		if( $upload_res !== true ) 
		{
			$error[] = 'ファイルのアップロードに失敗しました。';
		} else {
			$clean['attachment_file'] = $_FILES['attachment_file']['name'];
		}
	}

	if( empty($error) ) 
	{
	$page_flag = 1;
	// セッションの書き込み
	session_start();
	$_SESSION['page'] = true;
	}
} elseif( !empty($_POST['btn_submit']) )
{
	session_start();
	if( !empty($_SESSION['page']) && $_SESSION['page'] === true )
	{

		// セッションの削除
		unset($_SESSION['page']);
	
		$page_flag = 2;

		// 変数とタイムゾーンを初期化
		$header = null;
		$body = null;
		$auto_reply_subject = null;
		$auto_reply_text = null;
		$admin_reply_subject = null;
		$admin_reply_text = null;
		date_default_timezone_set('Asia/Tokyo');

		//日本語の使用宣言
		//mb_language("ja");
		//mb_internal_encoding("UTF-8");
		// 文字エンコードを指定
		mb_language('uni');
		mb_internal_encoding('UTF-8');

		// インスタンスを生成（true指定で例外を有効化）
		$mail = new PHPMailer(true);

		// 文字エンコードを指定
		$mail->CharSet = 'utf-8';

		try {
		// デバッグ設定
		// $mail->SMTPDebug = 2; // デバッグ出力を有効化（レベルを指定）
		// $mail->Debugoutput = function($str, $level) {echo "debug level $level; message: $str<br>";};
	  
		// SMTPサーバの設定
		$mail->isSMTP();                          // SMTPの使用宣言
		$mail->Host       = 'smtp.gmail.com';   // SMTPサーバーを指定
		$mail->SMTPAuth   = true;                 // SMTP authenticationを有効化
		$mail->Username   = 'lotte04715923210@gmail.com';   // SMTPサーバーのユーザ名
		$mail->Password   = 'lotte0471';           // SMTPサーバーのパスワード
		$mail->SMTPSecure = 'tls';  // 暗号化を有効（tls or ssl）無効の場合はfalse
		$mail->Port       = 465; // TCPポートを指定（tlsの場合は465や587）
	  
		// 送受信先設定（第二引数は省略可）
		$mail->setFrom('from@example.com', '差出人名'); // 送信者
		$mail->addAddress('to@xxxx.com', '受信者名');   // 宛先
		$mail->addReplyTo('replay@example.com', 'お問い合わせ'); // 返信先
		$mail->addCC('cc@example.com', '受信者名'); // CC宛先
		$mail->Sender = 'return@example.com'; // Return-path
	  
		// 送信内容設定

		// 件名を設定
	$auto_reply_subject = 'お問い合わせありがとうございます。';

	// 本文を設定
	$auto_reply_text = "この度は、お問い合わせ頂き誠にありがとうございます。下記の内容でお問い合わせを受け付けました。\n\n";
	$auto_reply_text .= "お問い合わせ日時：" . date("Y-m-d H:i") . "\n";
	$auto_reply_text .= "氏名：" . $_POST['your_name'] . "\n";
	$auto_reply_text .= "メールアドレス：" . $_POST['email'] . "\n\n";
	$auto_reply_text .= "年齢:" . $_POST['age'] . "\n";
	  
		// 送信
		$mail->send();
	    } catch (Exception $e) {
		// エラーの場合
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	  }
	  //DBに書き込む処理
	  
	}
}
function validation($data) {

	$error = array();

	// 氏名のバリデーション
	if( empty($data['your_name']) ) {
		$error[] = "「氏名」は必ず入力してください。";
	}elseif( 20 < mb_strlen($data['your_name']) ) {
		$error[] = "「氏名」は20文字以内で入力してください。";
	}
	// メールアドレスのバリデーション
	if( empty($data['email']) ) {
		$error[] = "「メールアドレス」は必ず入力してください。";
	}elseif( !preg_match( '/^[0-9a-z_.\/?-]+@([0-9a-z-]+\.)+[0-9a-z-]+$/', $data['email']) ) {
		$error[] = "「メールアドレス」は正しい形式で入力してください。";
	}


	// 性別のバリデーション
	if( empty($data['gender']) ) {
		$error[] = "「性別」は必ず入力してください。";
	}elseif( $data['gender'] !== 'male' && $data['gender'] !== 'female' ) {
		$error[] = "「性別」は必ず入力してください。";
	}

	// 年齢のバリデーション
	if( empty($data['age']) ) {
		$error[] = "「年齢」は必ず入力してください。";
	}elseif( (int)$data['age'] < 1 || 6 < (int)$data['age'] ) {
		$error[] = "「年齢」は必ず入力してください。";
	}

	// お問い合わせ内容のバリデーション
	if( empty($data['contact']) ) {
		$error[] = "「お問い合わせ内容」は必ず入力してください。";
	}

	// プライバシーポリシー同意のバリデーション
	if( empty($data['agreement']) ) {
		$error[] = "プライバシーポリシーをご確認ください。";
	}elseif( (int)$data['agreement'] !== 1 ) {
		$error[] = "プライバシーポリシーをご確認ください。";
	}

	return $error;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<title>お問い合わせフォーム</title>
<link rel="stylesheet" href="index1.css">

</head>
<body>
<h1>お問い合わせフォーム</h1>
<?php if( $page_flag === 1 ): ?>

<form method="post" action="">
	<div class="element_wrap">
		<label>氏名</label>
		<p><?php echo $_POST['your_name']; ?></p>
	</div>
	<div class="element_wrap">
		<label>メールアドレス</label>
		<p><?php echo $_POST['email']; ?></p>
	</div>
	<div class="element_wrap">
		<label>性別</label>
		<p><?php if( $_POST['gender'] === "male" ){ echo '男性'; }
		else{ echo '女性'; } ?></p>
	</div>
	<div class="element_wrap">
		<label>年齢</label>
		<p><?php if( $_POST['age'] === "1" ){ echo '〜19歳'; }
		elseif( $_POST['age'] === "2" ){ echo '20歳〜29歳'; }
		elseif( $_POST['age'] === "3" ){ echo '30歳〜39歳'; }
		elseif( $_POST['age'] === "4" ){ echo '40歳〜49歳'; }
		elseif( $_POST['age'] === "5" ){ echo '50歳〜59歳'; }
		elseif( $_POST['age'] === "6" ){ echo '60歳〜'; } ?></p>
	</div>
	<div class="element_wrap">
		<label>お問い合わせ内容</label>
		<p><?php echo nl2br($_POST['contact']); ?></p>
	</div>
	<?php if( !empty($clean['attachment_file']) ): ?>
	<div class="element_wrap">
		<label>画像ファイルの添付</label>
		<p><img src="<?php echo FILE_DIR.$clean['attachment_file']; ?>"></p>
	</div>
	<?php endif; ?>
	<div class="element_wrap">
		<label>プライバシーポリシーに同意する</label>
		<p><?php if( $_POST['agreement'] === "1" ){ echo '同意する'; }
		else{ echo '同意しない'; } ?></p>
	</div>
	<input type="submit" name="btn_back" value="戻る">
	<input type="submit" name="btn_submit" value="送信">
	<input type="hidden" name="your_name" value="<?php echo $_POST['your_name']; ?>">
	<input type="hidden" name="email" value="<?php echo $_POST['email']; ?>">
	<input type="hidden" name="gender" value="<?php echo $_POST['gender']; ?>">
	<input type="hidden" name="age" value="<?php echo $_POST['age']; ?>">
	<input type="hidden" name="contact" value="<?php echo $_POST['contact']; ?>">
	<?php if( !empty($clean['attachment_file']) ): ?>
		<input type="hidden" name="attachment_file" value="<?php echo $clean['attachment_file']; ?>">
	<?php endif; ?>
	<input type="hidden" name="agreement" value="<?php echo $_POST['agreement']; ?>">
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

<form method="post" action=""enctype="multipart/form-data">
	<div class="element_wrap">
		<label>氏名</label>
		<input type="text" name="your_name" value="<?php if( !empty($_POST['your_name']) ){ echo 
			$_POST['your_name']; } ?>">
	</div>
	<div class="element_wrap">
		<label>メールアドレス</label>
		<input type="text" name="email" value="<?php if( !empty($_POST['email']) ){ echo 
			$_POST['email']; } ?>">
	</div>
	<div class="element_wrap">
		<label>性別</label>
		<label for="gender_male"><input id="gender_male" type="radio" name="gender" value="male"<?php if
		( !empty($_POST['gender']) && $_POST['gender'] === "male" ){ echo 'checked'; } ?>>男性</label>
		<label for="gender_female"><input id="gender_female" type="radio" name="gender" value="female"<?php if
		( !empty($_POST['gender']) && $_POST['gender'] === "female" ){ echo 'checked'; } ?>>女性</label>
	</div>
	<div class="element_wrap">
		<label>年齢</label>
		<select name="age">
			<option value="">選択してください</option>
			<option value="1"<?php if( !empty($_POST['age']) && $_POST['age'] === "1" ){ echo 'selected';
			 } ?>>〜19歳</option>
			<option value="2"<?php if( !empty($_POST['age']) && $_POST['age'] === "2" ){ echo 'selected';
			 } ?>>20歳〜29歳</option>
			<option value="3"<?php if( !empty($_POST['age']) && $_POST['age'] === "3" ){ echo 'selected';
			 } ?>>30歳〜39歳</option>
			<option value="4"<?php if( !empty($_POST['age']) && $_POST['age'] === "4" ){ echo 'selected';
			 } ?>>40歳〜49歳</option>
			<option value="5"<?php if( !empty($_POST['age']) && $_POST['age'] === "5" ){ echo 'selected';
			 } ?>>50歳〜59歳</option>
			<option value="6"<?php if( !empty($_POST['age']) && $_POST['age'] === "6" ){ echo 'selected';
			 } ?>>60歳〜</option>
		</select>
	</div>
	<div class="element_wrap">
		<label>お問い合わせ内容</label>
		<textarea name="contact"><?php if( !empty($_POST['contact']) ){ echo $_POST['contact']; } ?></textarea>
	</div>
	<div class="element_wrap">
		<label>画像ファイルの添付</label>
		<input type="file" name="attachment_file">
	</div>
	<div class="element_wrap">
		<label for="agreement"><input id="agreement" type="checkbox" name="agreement" value="1"<?php if( 
			!empty($_POST['agreement']) && $_POST['agreement'] === "1" ){ echo 'checked'; } ?>>プライバシーポリシー
			に同意する</label>
	</div>
	<input type="submit" name="btn_confirm" value="入力内容を確認する">
</form>

<?php endif; ?>
</body>
</html>
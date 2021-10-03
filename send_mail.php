<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// 設置した場所のパスを指定する
require('HTDOCS/form/PHPMailer/src/PHPMailer.php');
require('HTDOCS/form/PHPMailer/src/Exception.php');
require('HTDOCS/form/PHPMailer/src/SMTP.php');
define( "FILE_DIR", "images/test/");
// Composerを利用した場合、requireは下記だけでOK
// require('path/to/vendor/autoload.php');
:
:
// 続きの処理
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
  $mail->Subject = '件名'; 
  $mail->Body    = 'メッセージ本文';  

  // 送信
  $mail->send();
} catch (Exception $e) {
  // エラーの場合
  echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
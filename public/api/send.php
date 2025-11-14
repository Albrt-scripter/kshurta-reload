<?php

// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST method is allowed']);
    exit;
}



$input = file_get_contents('php://input');
$data = json_decode($input, true) ?? $_POST;

$fullname = htmlspecialchars(trim($data["name"]), ENT_QUOTES, 'UTF-8');
$phonenumber = htmlspecialchars(preg_replace('/\D/', '', $data["phonenumber"]), ENT_QUOTES, 'UTF-8');

$errors = [];
if (empty($phonenumber))
    $errors['phonenumber'] = 'Телефон обязательное поле!';
if (empty($fullname))
    $errors['name'] = 'Имя обязательно!';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode($errors, JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    die();
}

$message = "
    <h2><strong>Сообщение с лендинга</strong>. Заявка с формы обратной связи</h2>
    <p><strong>Имя</strong>:</p>
    <h2>$fullname</h2>
    <p><strong>Телефон</strong>:</p>
    <h2><a href='tel:$phonenumber'>$phonenumber</a></h2>
";

use PHPMailer\PHPMailer\PHPMailer;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';


$env = parse_ini_file(__DIR__ . '/.env');
if (!$env) {
    echo json_encode(['error' => 'Failed to load .env']);
    exit;
}

try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->CharSet = "UTF-8";
    $mail->SMTPAuth = true;

    // $mail->SMTPDebug = 2;
    // $mail->Debugoutput = function ($str, $level) {
    //     file_put_contents('smtp_debug.log', "$level: $str\n", FILE_APPEND);
    // };

    $mail->Host = $env["SMTP_HOST"];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = $env["SMTP_PORT"];
    $mail->Username = $env["SMTP_USERNAME"];
    $mail->Password = $env["SMTP_PASSWORD"];
    $mail->setFrom($env["SMTP_FROM"], 'Заявка с лендинга');
    $mail->addAddress($env["SMTP_TO"]);
    $mail->Subject = 'Седой Кшурт - заявка с формы обратной связи';
    $mail->isHTML(true);
    $mail->Body = $message;

    $ok = false;

    if ($mail->send()) {
        $ok = true;
    }

    if ($ok) {
        http_response_code(200);
        echo json_encode(['result' => 'Sent letter']);
        die();
    } else {
        http_response_code(422);
        echo json_encode(['result' => 'false', 'error' => $mail->ErrorInfo]);
        die();
    }

} catch (Exception $e) {
    http_response_code(422);
    echo json_encode(['result' => 'false', 'error' => $e->getMessage()]);
    die();
}

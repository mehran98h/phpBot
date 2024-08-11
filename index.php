<?php

$host = 'localhost';
$db = 'isbdir11_telegram_bot';
$user = 'isbdir11_bot';
$pass = 'Q)vyzySfHu_O';
$token = "677733170:AAFiuuvrflwBTbZtHkbUN5zWSoFld1cfo5I";

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    exit;
}

if (isset($update["message"])) {
    $message = $update["message"];
    $chat_id = $message["chat"]["id"];
    $text = $message["text"];

    if (preg_match('/^\/translate (\w{2}) (.+)/', $text, $matches)) {
        $target_lang = $matches[1]; // زبان مقصد
        $input_text = $matches[2]; // متن برای ترجمه

        $translated_text = googleTranslate($input_text, "fa",$target_lang);
        sendMessage($chat_id, $translated_text);
    } else {
        sendMessage($chat_id, "لطفاً از فرمت صحیح استفاده کنید:\n/translate [کد زبان] [متن]");
    }
}
function googleTranslate($text, $sourceLang, $targetLang) {
    $text = urlencode($text);
    $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=" . $sourceLang . "&tl=" . $targetLang . "&dt=t&q=" . $text;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // غیرفعال کردن بررسی SSL برای تست (در محیط های توسعه)
    $response = curl_exec($ch);

    if(curl_errno($ch)) {
        $error_msg = curl_error($ch);
        file_put_contents("log.txt", "cURL error: " . $error_msg . "\n", FILE_APPEND);
        curl_close($ch);
        return "خطا در دریافت ترجمه.";
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    file_put_contents("log.txt", "HTTP Code: " . $http_code . "\n", FILE_APPEND);
    file_put_contents("log.txt", "Response from Google API: " . $response . "\n", FILE_APPEND);

    $result = json_decode($response, true);
    $translatedText = $result[0][0][0] ?? "خطا در ترجمه.";

    return $translatedText;
}




function sendMessage($chat_id, $message) {
    global $token;
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $post_fields = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'Markdown',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $output = curl_exec($ch);
    curl_close($ch);

    return $output;
}

?>

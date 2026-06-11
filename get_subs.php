<?php
// Разрешаем запросы со страницы
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Включаем отображение ошибок для диагностики
ini_set('display_errors', 1);
error_reporting(E_ALL);

// !!! ВСТАВЬ СЮДА СВОЙ ТОКЕН ОТ BOTFAHNER !!!
$botToken = "8620515294:AAHXKaZEtSxBaVA6EDSvikb4Pn1XA4OVLI0"; 

// Получаем имя канала и очищаем от случайных знаков @
$channel = isset($_GET['channel']) ? trim($_GET['channel']) : '';
$channel = ltrim($channel, '@');

// Список разрешенных каналов
$allowed_channels = ['Business_rules', 'biz_people', 'all_about_busines'];

if (!in_array($channel, $allowed_channels)) {
    echo json_encode(['success' => false, 'message' => 'Канал не разрешен в скрипте: ' . htmlspecialchars($channel)]);
    exit;
}

$url = "https://api.telegram.org/bot{$botToken}/getChatMembersCount?chat_id=@{$channel}";

// Используем cURL вместо file_get_contents (он работает стабильнее и быстрее)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Игнорируем проблемы с SSL хостинга

$response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

// Если cURL вообще не смог отправить запрос
if ($response === false) {
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера хостинга (cURL): ' . $curl_error]);
    exit;
}

$data = json_decode($response, true);

if (isset($data['ok']) && $data['ok'] == true) {
    // Всё отлично, отдаем число
    echo json_encode(['success' => true, 'count' => $data['result']]);
} else {
    // Телеграм вернул ошибку (например, бот не админ или токен неверный)
    $tg_error = isset($data['description']) ? $data['description'] : 'Неизвестная ошибка API';
    echo json_encode(['success' => false, 'message' => 'Ошибка от Telegram: ' . $tg_error]);
}
?>

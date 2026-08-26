<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

return [
    'operator_name' => 'ООО «ВИЛМЕД»',
    'operator_short' => 'ООО «ВИЛМЕД»',
    'operator_legal_form' => 'ООО',
    'inn' => '3662302802',
    'ogrn' => '1223600020599',
    'kpp' => '366201001',
    'site' => 'https://oftal-med.ru/',
    'site_host' => 'oftal-med.ru',
    'email' => 'info@oftal-med.ru',
    'phone' => '8 (800) 555-55-50',
    'phone_tel' => '88005555550',
    'address_legal' => '394026, Россия, Воронежская обл., г. Воронеж, пр-кт Московский, д. 19, помещ. 1/19',
    'images' => [
        'consent' => '/upload/soglasie-na-obrabotku-personalnyh-dannyh-oftalmed.jpg',
        'personal_data' => '/upload/politics.jpg',
        'cookie' => '/upload/politika-ispolzovanija-cookies-oftalmed.jpg',
        'recommendation' => '/upload/rules-recommendation.jpg',
    ],
    'third_parties' => include __DIR__ . '/third_parties_data.php',
];

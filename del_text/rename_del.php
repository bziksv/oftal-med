<?
// Отключаем ненужные функции Битрикс
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
define("STOP_STATISTICS", true);

// Отключаем вывод Notice-ошибок
error_reporting(E_ALL & ~E_NOTICE);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER;

if ($USER->IsAdmin()):

    CModule::IncludeModule("iblock");
    
    $IBLOCK_ID = 33;
    $processed = 0;
    $errors = 0;
    
    echo "<h3>Удаление PRIME из названий</h3>";
    echo "<style>
        .container {max-width:1200px; margin:20px auto; padding:20px; background:#fff; box-shadow:0 0 10px rgba(0,0,0,0.1);}
        .element {border:1px solid #ddd; padding:15px; margin-bottom:15px;}
        .success {color:#4CAF50; font-weight:bold;}
        .error {color:#F44336; font-weight:bold;}
    </style>";
    echo "<div class='container'>";
    
    // Известные ID элементов с PRIME
    $targetIds = [
		// 24521, // Тестовый элемент
        // Добавьте другие ID по мере необходимости
    ];
    
    // Если нужно обработать все элементы с PRIME
    if (empty($targetIds)) {
        $res = CIBlockElement::GetList(
            ["ID" => "ASC"],
            ["IBLOCK_ID" => $IBLOCK_ID, "NAME" => "%PRIME%"],
            false,
            false,
            ["ID"]
        );
        while ($element = $res->Fetch()) {
            $targetIds[] = $element["ID"];
        }
    }
    
    if (empty($targetIds)) {
        echo "<div class='error'>Не найдено элементов с PRIME в названии!</div>";
    } else {
        echo "<p>Найдено элементов для обработки: " . count($targetIds) . "</p>";
        
        foreach ($targetIds as $elementId) {
            $element = CIBlockElement::GetByID($elementId)->Fetch();
            
            if (!$element) {
                echo "<div class='error'>Элемент ID $elementId не найден!</div>";
                continue;
            }
            
            $originalName = $element["NAME"];
            echo "<div class='element'>";
            echo "<h4>Обработка элемента ID: $elementId</h4>";
            echo "<p><strong>Оригинальное название:</strong><br>" . htmlspecialchars($originalName) . "</p>";
            
            // Удаляем PRIME из названия
            $newName = str_ireplace('PRIME', '', $originalName);
            $newName = preg_replace('/\s+/', ' ', $newName); // Удаляем двойные пробелы
            $newName = trim($newName);
            
            if ($newName !== $originalName) {
                $el = new CIBlockElement;
                $result = $el->Update($elementId, ["NAME" => $newName]);
                
                if ($result) {
                    $processed++;
                    echo "<p class='success'>УСПЕШНО: Изменено на:<br>" . htmlspecialchars($newName) . "</p>";
                } else {
                    $errors++;
                    echo "<p class='error'>ОШИБКА: " . $el->LAST_ERROR . "</p>";
                }
            } else {
                echo "<p>НЕ ИЗМЕНЕНО: Название не содержит PRIME</p>";
            }
            
            echo "</div>";
        }
        
        echo "<h3>Итоги обработки</h3>";
        echo "<p>Обработано элементов: $processed</p>";
        echo "<p>Ошибок: $errors</p>";
    }
    
    echo "</div>"; // закрываем container
    die(); // Останавливаем выполнение, чтобы избежать посторонних ошибок

endif;
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
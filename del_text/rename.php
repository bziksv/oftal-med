<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER;
if ($USER->IsAdmin()):

    CModule::IncludeModule("iblock");
    
    $IBLOCK_ID = 33;
    $suffix = " PRIME";
    $chunkSize = 20; // Количество элементов за один запуск
    $processed = 0;
    $errors = 0;
    
    // Получаем текущую позицию из параметра или начинаем сначала
    $lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
    
    // Выбираем элементы порциями
    $res = CIBlockElement::GetList(
        ["ID" => "ASC"],
        [
            "IBLOCK_ID" => $IBLOCK_ID,
            "ID" => ">$lastId", // Начинаем с последнего обработанного ID
            "CHECK_PERMISSIONS" => "N"
        ],
        false,
        ["nTopCount" => $chunkSize],
        ["ID", "NAME"]
    );
    
    $maxId = 0; // Для отслеживания последнего обработанного ID
    
    while ($element = $res->Fetch()) {
        $maxId = $element["ID"];
        $newName = $element["NAME"];
        
        // Удаляем все повторения "PRIME" в конце названия
        $newName = preg_replace('/( PRIME)+$/', '', $newName);
        
        // Добавляем "PRIME" только если его еще нет
        if (substr($newName, -strlen($suffix)) !== $suffix) {
            $newName .= $suffix;
        }
        
        // Обновляем только если имя изменилось
        if ($newName !== $element["NAME"]) {
            $el = new CIBlockElement;
            $result = $el->Update($element["ID"], ["NAME" => $newName]);
            
            if ($result) {
                $processed++;
                echo "Обновлен ID: {$element['ID']} - '$newName'<br>";
            } else {
                $errors++;
                echo "<span style='color:red'>Ошибка ID: {$element['ID']} - {$el->LAST_ERROR}</span><br>";
            }
        }
    }
    
    // Определяем, нужно ли продолжать
    if ($maxId > 0) {
        // Продолжаем обработку со следующего элемента
        $nextUrl = $APPLICATION->GetCurPageParam("last_id=$maxId", ["last_id"]);
        ?>
        <script>
            setTimeout(function() {
                window.location.href = '<?=$nextUrl?>';
            }, 500); // Пауза 0.5 сек между порциями
        </script>
        <?
        echo "<p>Обработано $processed элементов. Продолжаем...</p>";
    } else {
        echo "<h3>Обработка завершена!</h3>";
        echo "Всего обработано элементов: $processed<br>";
        echo "Ошибок: $errors<br>";
        echo "<p><a href='{$APPLICATION->GetCurPage()}'>Начать сначала</a></p>";
    }

endif;
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
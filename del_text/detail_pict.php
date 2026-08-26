<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER;
if ($USER->IsAdmin()):

    CModule::IncludeModule("iblock");

    $back = true;
    $IBLOCK_ID = 33;
    $SECTION_ID = 0;

    // Увеличиваем время выполнения для больших объемов
    set_time_limit(0);
    
    $arSelect = Array("ID", "IBLOCK_ID", "DETAIL_PICTURE", "PROPERTY_MORE_PHOTO");
    $arFilter = Array(
        "IBLOCK_ID" => $IBLOCK_ID,
        "SECTION_ID" => $SECTION_ID,
        "INCLUDE_SUBSECTIONS" => "Y",
        array(
            "LOGIC" => "OR",
            "!DETAIL_PICTURE" => false,
            "!PROPERTY_MORE_PHOTO" => false
        )
    );
    
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    while($element = $res->GetNext())
    {
        $back = false;
        $elementId = $element["ID"];
        $updateNeeded = false;
        $arLoadProductArray = array();

        // 1. Удаляем DETAIL_PICTURE
        if($element['DETAIL_PICTURE'])
        {
            $arLoadProductArray["DETAIL_PICTURE"] = ['del' => 'Y'];
            $updateNeeded = true;
            echo "Удаляем DETAIL_PICTURE для элемента $elementId<br>";
        }

        // 2. Удаляем MORE_PHOTO
        if(!empty($element['PROPERTY_MORE_PHOTO_VALUE']))
        {
            // Специальный способ очистки множественного свойства
            CIBlockElement::SetPropertyValuesEx(
                $elementId,
                $IBLOCK_ID,
                array('MORE_PHOTO' => array("VALUE" => array("del" => "Y")))
            );
            echo "Удаляем MORE_PHOTO для элемента $elementId (".count($element['PROPERTY_MORE_PHOTO_VALUE'])." фото)<br>";
        }

        // Обновляем элемент если нужно
        if($updateNeeded)
        {
            $el = new CIBlockElement;
            $result = $el->Update($elementId, $arLoadProductArray);
            
            if(!$result)
            {
                echo "Ошибка при обновлении элемента $elementId: ".$el->LAST_ERROR."<br>";
            }
        }
        
        echo "<hr>";
    }

    if($back):
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.location.href = '/del_text/';
            });
        </script>
        <?
    else:
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.location.href = '/del_text/detail_pict.php';
            });
        </script>
        <?
    endif;
endif;
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
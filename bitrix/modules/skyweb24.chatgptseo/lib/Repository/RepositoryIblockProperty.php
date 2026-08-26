<?php

namespace Skyweb24\ChatgptSeo\Repository;

use CIBlockElement;
use CIBlockProperty;
use Skyweb24\Chatgptseo\Enum\EnumPropertyType;

class RepositoryIblockProperty
{
    public function getById(int $iblockId, array $propertyTypeList): array
    {
        $properties = CIBlockProperty::GetList(["sort" => "asc"], ["ACTIVE" => "Y", "IBLOCK_ID" => $iblockId]);
        while ($prop = $properties->GetNext()) {
            if (in_array($prop["PROPERTY_TYPE"], $propertyTypeList)) {
                $r = [
                    "code" => $prop["CODE"],
                    "name" => $prop["NAME"],
                    "type" => $prop["PROPERTY_TYPE"],
                ];

                if ($prop["PROPERTY_TYPE"] === EnumPropertyType::ELEMENT->value) {
                    $r['iblockId'] = $prop["LINK_IBLOCK_ID"];
                }

                $result[] = $r;
            }
        }
        return ($result ?? []);
    }

    public function update(int $elementId, array $updateData)
    {
        CIBlockElement::SetPropertyValuesEx(
            $elementId,
            false,
            $updateData,
        );
    }
}
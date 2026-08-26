<?php

namespace Skyweb24\ChatgptSeo\Resource;

use Skyweb24\Chatgptseo\Enum\EnumPropertyType;
use Skyweb24\ChatgptSeo\Interface\InterfaceResourceQueryBuilderRule;
use Skyweb24\ChatgptSeo\Service\TaskFormField\Field\FieldAbstract;
use Skyweb24\ChatgptSeo\Service\TaskFormField\Option\Entity\EntityAbstract;
use Skyweb24\ChatgptSeo\Service\TaskFormField\Option\Feature\FeatureAbstract;
use Skyweb24\ChatgptSeo\Service\TaskFormField\Option\Incorrect\IncorrectPattern;
use Skyweb24\ChatgptSeo\Service\TaskFormField\Option\Incorrect\IncorrectPatternProperty;
use Skyweb24\ChatgptSeo\Service\TaskFormField\Option\OptionAbstract;
use Skyweb24\ChatgptSeo\Service\TaskFormField\OptionGroup\OptionGroup;

class ResourceQueryBuilderRule implements InterfaceResourceQueryBuilderRule
{
    /** @param FieldAbstract[] $entityList */
    public function __construct(protected array $entityList)
    {
    }

    // TODO REFACTORING необходимо чтобы каждый $entity мог сам отдавать список
    public function toArray(): array
    {
        foreach ($this->entityList as $entity) {
            foreach ($entity->getOptionList() as $option) {
                if ($option instanceof OptionGroup) {

                    $rows = [];

                    /** @var OptionAbstract $groupOption */
                    foreach ($option->getOptionList() as $groupOption) {
                        $row = [
                            "code" => $groupOption->getCode(),
                            "name" => $groupOption->getName(),
                        ];

                        if ($groupOption instanceof IncorrectPatternProperty) {
                            $row["type"] = $groupOption->getType();
                            if ($row["type"] == EnumPropertyType::ELEMENT->value) {
                                $row['option_list'] = $this->buildOptionListForIncorrectPropertyElement(
                                    $groupOption->getOptionList()
                                );
                            }
                        }

                        $rows[] = $row;
                    }

                    $result[$entity->getCode()][] = [
                        "code" => $option->getCode(),
                        "name" => $option->getName(),
                        "option_list" => $rows ?? [],
                    ];

                } else if ($option instanceof EntityAbstract) {

                    $rows = [];
                    /** @var FeatureAbstract $featureOption */
                    foreach ($option->getFeatureList() as $featureOption) {
                        $rows[] = [
                            "code" => $featureOption->getCode(),
                            "name" => $featureOption->getName(),
                        ];
                    }

                    $result[$entity->getCode()][] = [
                        "code" => $option->getCode(),
                        "name" => $option->getName(),
                        "feature_list" => $rows ?? [],
                    ];

                } else {
                    $result[$entity->getCode()][] = [
                        "code" => $option->getCode(),
                        "name" => $option->getName(),
                    ];
                }
            }
        }

        return $result ?? [];
    }

    /**
     * @param IncorrectPatternProperty[] $optionList
     * @return array
     */
    protected function buildOptionListForIncorrectPropertyElement(array $optionList): array
    {
        $newOptionList = [];

        foreach ($optionList as $option) {
            if ($option instanceof IncorrectPattern) {
                $newOptionList[] = [
                    'code' => $option->getCode(),
                    'name' => $option->getName(),
                ];
            } else {
                $newOptionList[] = $option;
            }
        }

        return $newOptionList;
    }
}
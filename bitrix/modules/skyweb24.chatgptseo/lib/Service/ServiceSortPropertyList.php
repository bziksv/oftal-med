<?php

namespace Skyweb24\Chatgptseo\Service;

class ServiceSortPropertyList
{
    protected array $propertyList;

    public function __construct(array $propertyList)
    {
        $this->setPropertyList($propertyList);
        $this->sortPropertyList();
    }

    /**
     * @param array $propertyList
     * @return ServiceSortPropertyList
     */
    public function setPropertyList(array $propertyList): ServiceSortPropertyList
    {
        $this->propertyList = $propertyList;
        return $this;
    }

    /**
     * @return array
     */
    public function getPropertyList(): array
    {
        return $this->propertyList;
    }

    protected function sortPropertyList(): static
    {
        $order = [
            "E" => 1,
            "L" => 2,
            "S" => 3,
            "N" => 4,
        ];

        usort($this->propertyList, function ($a, $b) use ($order) {
            return $order[$a['type']] <=> $order[$b['type']];
        });

        return $this;
    }
}

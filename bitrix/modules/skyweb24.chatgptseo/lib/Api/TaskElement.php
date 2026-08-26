<?php

namespace Skyweb24\ChatgptSeo\Api;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Skyweb24\ChatgptSeo\Repository\RepositoryTaskElement;
use Skyweb24\ChatgptSeo\Service\TaskElement\ServiceTaskElementCreate;
use Skyweb24\ChatgptSeo\Validator\ValidatorTaskElement;

class TaskElement
{
    /**
     * @throws ArgumentException
     */
    public static function getByTaskId(int $taskId, array $statusIdList = []): array|false
    {
        /** @var RepositoryTaskElement $repositoryTaskElement */
        $repositoryTaskElement = ServiceLocator::getInstance()->get(RepositoryTaskElement::class);

        if ((new ValidatorTaskElement())->isValidForGetById($taskId)) {
            $dtoTaskElementList = $repositoryTaskElement->getAllByTaskId($taskId, $statusIdList);

            foreach ($dtoTaskElementList as $dtoTaskElement) {
                $result[] = [
                    'id' => $dtoTaskElement->id,
                    'task_id' => $dtoTaskElement->task_id,
                    'element_id' => $dtoTaskElement->element_id,
                    'status_id' => $dtoTaskElement->status_id,
                ];
            }

            if (!empty($result)) {
                return $result;
            }

            return false;
        }

        return false;
    }

    /**
     * @throws ArgumentException
     */
    public static function add(int $taskId, int $elementId): bool|int
    {
        /** @var ServiceTaskElementCreate $serviceTaskElementCreate */
        $serviceTaskElementCreate = ServiceLocator::getInstance()->get(ServiceTaskElementCreate::class);

        if ((new ValidatorTaskElement())->isValidForAdd($taskId, $elementId)) {
            return $serviceTaskElementCreate->addElementIdByTaskId($elementId, $taskId);
        }

        return false;
    }

}
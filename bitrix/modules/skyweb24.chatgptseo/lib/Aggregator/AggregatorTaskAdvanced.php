<?php

namespace Skyweb24\ChatgptSeo\Aggregator;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Web\Json;
use Skyweb24\ChatgptSeo\Dto\DtoTaskAdvanced;
use Skyweb24\ChatgptSeo\Enum\EnumStatus;
use Skyweb24\ChatgptSeo\Model\ModelTasksTable;
use Skyweb24\ChatgptSeo\Repository\RepositoryTask;
use Skyweb24\ChatgptSeo\Repository\RepositoryTaskElement;

class AggregatorTaskAdvanced
{
    public function __construct(
        protected RepositoryTask        $repositoryTask,
        protected RepositoryTaskElement $repositoryTaskElement
    )
    {
    }

    public function getById(int $taskId): DtoTaskAdvanced|false
    {
        if (!$dtoTask = $this->repositoryTask->getById($taskId)) {
            return false;
        }

        $dtoTaskAdvanced = new DtoTaskAdvanced(
            id: $dtoTask->id,
            user_id: $dtoTask->user_id,
            iblock_id: $dtoTask->iblock_id,
            date_create: $dtoTask->date_create,
            date_complete: $dtoTask->date_complete,
            status_id: $dtoTask->status_id,
            operation_type: $dtoTask->operation_type,
            element_type: $dtoTask->element_type,
            incorrect_text: $dtoTask->incorrect_text,
            operations: $dtoTask->operations ? Json::decode($dtoTask->operations) : null,
        );

        $dtoTaskElementList = $this->repositoryTaskElement->getAllByTaskId($dtoTask->id);

        foreach ($dtoTaskElementList as $dtoTaskElement) {
            $dtoTaskAdvanced->elements[] = $dtoTaskElement;
        }

        return $dtoTaskAdvanced;
    }

    public function getInProgressTaskElementsById(int $taskId): DtoTaskAdvanced|false
    {
        if (!$dtoTask = $this->repositoryTask->getById($taskId)) {
            return false;
        }

        $dtoTaskAdvanced = new DtoTaskAdvanced(
            id: $dtoTask->id,
            user_id: $dtoTask->user_id,
            iblock_id: $dtoTask->iblock_id,
            date_create: $dtoTask->date_create,
            date_complete: $dtoTask->date_complete,
            status_id: $dtoTask->status_id,
            operation_type: $dtoTask->operation_type,
            element_type: $dtoTask->element_type,
            incorrect_text: $dtoTask->incorrect_text,
            operations: $dtoTask->operations ? Json::decode($dtoTask->operations) : null,
        );

        $dtoTaskElementList = $this->repositoryTaskElement->getAllByTaskId($dtoTask->id);

        foreach ($dtoTaskElementList as $dtoTaskElement) {
            if ($dtoTaskElement->status_id == EnumStatus::PROGRESS) {
                $dtoTaskAdvanced->elements[] = $dtoTaskElement;
            }
        }

        return $dtoTaskAdvanced;
    }
}
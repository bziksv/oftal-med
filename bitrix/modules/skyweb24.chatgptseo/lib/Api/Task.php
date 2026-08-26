<?php

namespace Skyweb24\ChatgptSeo\Api;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Web\Json;
use Skyweb24\Chatgptseo\Controller\Index\ControllerTaskStart;
use Skyweb24\ChatgptSeo\Repository\RepositoryTask;
use Skyweb24\ChatgptSeo\Service\Task\ServiceTaskCreate;
use Skyweb24\ChatgptSeo\Validator\ValidatorTask;

class Task
{
    public static function list(array $arParams): array|false
    {
        /** @var RepositoryTask $repositoryTask */
        $repositoryTask = ServiceLocator::getInstance()->get(RepositoryTask::class);

        $filter = $arParams['filter'] ?? [];
        $sort = $arParams['sort'] ?? [];
        $select = $arParams['select'] ?? [];

        $response = $repositoryTask->getAll($filter, 0, 0, $sort, $select);
        $result = [];

        foreach ($response->fetchAll() as $row) {
            $r = [
                "id" => $row["id"] ?? null,
                "user_id" => $row["user_id"] ?? null,
                "iblock_id" => $row["iblock_id"] ?? null,
                "date_create" => $row["date_create"]?->toString() ?? null,
                "date_complete" => $row["date_complete"]?->toString() ?? null,
                "status_id" => $row["status_id"] ?? null,
                "operation_type" => $row["operation_type"] ?? null,
                "element_type" => $row["element_type"] ?? null,
                "incorrect_text" => $row["incorrect_text"] ?? null,
            ];

            if ($row["operations"]) {
                try {
                    $r["operations"] = Json::decode($row["operations"]);
                } catch (ArgumentException $e) {
                    $r["operations"] = [];
                }
            }

            $r = array_filter($r, function ($value) {
                return !is_null($value);
            });

            $result[] = $r;
        }

        return $result ?: false;
    }

    /**
     * @throws ArgumentException
     */
    public static function add(array $data): int|false|string
    {
        /** @var ServiceTaskCreate $serviceTaskCreate */
        $serviceTaskCreate = ServiceLocator::getInstance()->get(ServiceTaskCreate::class);

        if ((new ValidatorTask())->isValidForAdd($data)) {
            try {
                $data['operations'] = Json::encode($data['operations']);
            } catch (ArgumentException $e) {
                return false;
            }

            return $serviceTaskCreate->create($data);
        }

        return false;
    }

    public static function run(int $taskId): bool
    {
        (new ControllerTaskStart())->run($taskId);
        return false;
    }
}

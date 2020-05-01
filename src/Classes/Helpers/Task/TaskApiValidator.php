<?php

declare(strict_types=1);

namespace App\Classes\Helpers\Task;

use App\Entity\Task;

class TaskApiValidator
{
    /**
     * @param array $data
     *
     * @return bool
     */
    public function doDataFromAllFieldsExist(array $data): bool
    {
        foreach (Task::ALL_API_KEYS as $apiKey) {
            if (!isset($data[$apiKey]) || $data[$apiKey] === '')  {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function doesDataFromAtLeastOneFieldExist(array $data): bool
    {
        foreach (Task::ALL_API_KEYS as $apiKey) {
            if (isset($data[$apiKey]) && $data[$apiKey] !== '') {
                return true;
            }
        }

        return false;
    }
}

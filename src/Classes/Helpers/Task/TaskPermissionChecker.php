<?php

declare(strict_types=1);

namespace App\Classes\Helpers\Task;

use App\Entity\Task;
use App\Entity\User;

class TaskPermissionChecker
{
    /**
     * @param Task $task
     * @param User $user
     *
     * @return bool
     */
    public function canReadTask(Task $task, User $user): bool
    {
        return $task->getUser()->isSameUser($user)
            || $user->isAdmin();
    }

    /**
     * @param Task $task
     * @param User $user
     *
     * @return bool
     */
    public function canUpdateTask(Task $task, User $user): bool
    {
        return $task->getUser()->isSameUser($user)
            || $user->isAdmin();
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function canDeleteTask(User $user): bool
    {
        return $user->isAdmin();
    }
}

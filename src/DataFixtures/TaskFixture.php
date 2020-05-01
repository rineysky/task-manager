<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\TaskStatus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixture extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $firstUser = $this->getReference(UserFixture::FIRST_USER_REFERENCE);
        $startDate = new \DateTime('today');
        $dueDate = (new \DateTime('today'))->setTime(23, 59, 59);

        $manager->persist($this->createTask(
            $firstUser,
            clone $startDate,
            clone $dueDate,
            'The First Task',
            'The first task description'
        ));
        $manager->persist($this->createTask(
            $firstUser,
            clone $startDate,
            clone $dueDate,
            'The Second Task',
            'The second task description'
        ));
        $manager->persist($this->createTask(
            $firstUser,
            (clone $startDate)->modify('+1 day'),
            (clone $dueDate)->modify('+1 day'),
            'The Third Task',
            'The third task description'
        ));
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            UserFixture::class,
            TaskStatusFixture::class,
        ];
    }

    /**
     * @param User $user
     * @param \DateTime $startDate
     * @param \DateTime $dueDate
     * @param string $title
     * @param string $description
     *
     * @return Task
     */
    private function createTask(
        User $user,
        \DateTime $startDate,
        \DateTime $dueDate,
        string $title,
        string $description
    ): Task {
        $task = new Task();
        $task->setStatus($this->getReference(TaskStatus::PENDING));
        $task->setUser($user);
        $task->setStartDate($startDate);
        $task->setDueDate($dueDate);
        $task->setTitle($title);
        $task->setDescription($description);

        return $task;
    }
}

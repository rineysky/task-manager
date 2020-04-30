<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\TaskStatus;
use App\Entity\TaskTemplate;
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
        $secondUser = $this->getReference(UserFixture::SECOND_USER_REFERENCE);
        $firstTask = $this->getReference(TaskTemplateFixture::FIRST_TASK_REFERENCE);
        $secondTask = $this->getReference(TaskTemplateFixture::SECOND_TASK_REFERENCE);
        $thirdTask = $this->getReference(TaskTemplateFixture::THIRD_TASK_REFERENCE);
        $startDate = new \DateTime('today');
        $dueDate = (new \DateTime('today'))->setTime(23, 59, 59);

        $manager->persist($this->createTask($firstUser, $firstTask, clone $startDate, clone $dueDate));
        $manager->persist($this->createTask($firstUser, $secondTask, clone $startDate, clone $dueDate));
        $manager->persist($this->createTask($firstUser, $thirdTask, clone $startDate, clone $dueDate));
        $manager->persist($this->createTask($secondUser, $firstTask, clone $startDate, clone $dueDate));
        $manager->persist($this->createTask(
            $secondUser,
            $secondTask,
            (clone $startDate)->modify('+1 day'),
            (clone $dueDate)->modify('+1 day')
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
            TaskTemplateFixture::class,
        ];
    }

    /**
     * @param User $user
     * @param TaskTemplate $taskTemplate
     * @param \DateTime $startDate
     * @param \DateTime $dueDate
     *
     * @return Task
     */
    private function createTask(User $user, TaskTemplate $taskTemplate, \DateTime $startDate, \DateTime $dueDate): Task
    {
        $task = new Task();
        $task->setStatus($this->getReference(TaskStatus::PENDING));
        $task->setUser($user);
        $task->setTemplate($taskTemplate);
        $task->setStartDate($startDate);
        $task->setDueDate($dueDate);

        return $task;
    }
}

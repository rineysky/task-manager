<?php

declare(strict_types=1);

namespace App\Classes\Helpers\Task;

use App\Classes\Exceptions\InvalidDateTimeFormatException;
use App\Classes\Exceptions\MissingMandatoryParameterException;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TaskApiHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var TaskApiValidator
     */
    private $taskApiValidator;

    /**
     * @var TaskCreator
     */
    private $taskCreator;

    /**
     * @param EntityManagerInterface $em
     * @param TokenStorageInterface $tokenStorage
     * @param TaskApiValidator $taskApiValidator
     * @param TaskCreator $taskCreator
     */
    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        TaskApiValidator $taskApiValidator,
        TaskCreator $taskCreator
    ) {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->taskApiValidator = $taskApiValidator;
        $this->taskCreator = $taskCreator;
    }

    /**
     * @param array $data
     *
     * @throws InvalidDateTimeFormatException
     * @throws MissingMandatoryParameterException
     */
    public function createFromApi(array $data): void
    {
        if (!$this->taskApiValidator->doDataFromAllFieldsExist($data)) {
            throw new MissingMandatoryParameterException();
        }

        $task = $this->taskCreator->create(
            $this->tokenStorage->getToken()->getUser(),
            $this->createDateTimeFromString($data[Task::START_DATE_API_KEY]),
            $this->createDateTimeFromString($data[Task::DUE_DATE_API_KEY]),
            $data[Task::TITLE_API_KEY],
            $data[Task::DESCRIPTION_API_KEY]
        );

        $this->em->persist($task);
        $this->em->flush();
    }

    /**
     * @param Task $task
     * @param array $data
     *
     * @throws InvalidDateTimeFormatException
     * @throws MissingMandatoryParameterException
     */
    public function fullUpdateFromApi(Task $task, array $data): void
    {
        if (!$this->taskApiValidator->doDataFromAllFieldsExist($data)) {
            throw new MissingMandatoryParameterException();
        }

        $task->setStartDate($this->createDateTimeFromString($data[Task::START_DATE_API_KEY]));
        $task->setDueDate($this->createDateTimeFromString($data[Task::DUE_DATE_API_KEY]));
        $task->setTitle($data[Task::TITLE_API_KEY]);
        $task->setDescription($data[Task::DESCRIPTION_API_KEY]);

        $this->em->flush();
    }

    /**
     * @param Task $task
     * @param array $data
     *
     * @throws InvalidDateTimeFormatException
     * @throws MissingMandatoryParameterException
     */
    public function partialUpdateFromApi(Task $task, array $data): void
    {
        if (!$this->taskApiValidator->doesDataFromAtLeastOneFieldExist($data)) {
            throw new MissingMandatoryParameterException();
        }

        if (isset($data[Task::START_DATE_API_KEY])) {
            $task->setStartDate($this->createDateTimeFromString($data[Task::START_DATE_API_KEY]));
        }

        if (isset($data[Task::DUE_DATE_API_KEY])) {
            $task->setDueDate($this->createDateTimeFromString($data[Task::DUE_DATE_API_KEY]));
        }

        if (isset($data[Task::TITLE_API_KEY])) {
            $task->setTitle($data[Task::TITLE_API_KEY]);
        }

        if (isset($data[Task::DESCRIPTION_API_KEY])) {
            $task->setDescription($data[Task::DESCRIPTION_API_KEY]);
        }

        $this->em->flush();
    }

    /**
     * @param string $dateAsString
     * @return \DateTime
     *
     * @throws InvalidDateTimeFormatException
     */
    private function createDateTimeFromString(string $dateAsString): \DateTime
    {
        $dateTime = \DateTime::createFromFormat('d-m-Y H:i:s', $dateAsString);

        if (!$dateTime) {
            throw new InvalidDateTimeFormatException('d-m-Y H:i:s');
        }

        return $dateTime;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Classes\Exceptions\InvalidDateTimeFormatException;
use App\Classes\Exceptions\MissingMandatoryParameterException;
use App\Classes\Helpers\Task\TaskApiHandler;
use App\Classes\Helpers\Task\TaskPermissionChecker;
use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class TaskController extends AbstractApiController
{
    /**
     * @Route("/tasks", name="get_all_tasks", methods={"GET"})
     *
     * @param Request $request
     * @param TaskRepository $taskRepository
     *
     * @return JsonResponse
     */
    public function getAll(Request $request, TaskRepository $taskRepository): JsonResponse
    {
        $startDateParameter = $request->query->get(Task::START_DATE_API_KEY);
        $dueDateParameter = $request->query->get(Task::DUE_DATE_API_KEY);

        if (null === $startDateParameter && null === $dueDateParameter) {
            $startDate = new \DateTime('today');
            $dueDate = (new \DateTime('today'))->setTime(23, 59, 59);
        } else {
            if (null !== $startDateParameter) {
                $startDate = \DateTime::createFromFormat('d-m-Y H:i:s', $startDateParameter);
            }

            if (null !== $dueDateParameter) {
                $dueDate = \DateTime::createFromFormat('d-m-Y H:i:s', $dueDateParameter);
            }
        }

        $data = \array_map(
            static function (Task $task) {
                return [
                    'id' => $task->getId(),
                    'title' => $task->getTitle(),
                    'status' => $task->getStatus()->getDescription(),
                    'startDate' => $task->getStartDate()->format('d-m-Y H:i:s'),
                    'dueDate' => $task->getDueDate()->format('d-m-Y H:i:s'),
                ];
            },
            $taskRepository->getActiveByUserAndDates(
                $this->getUser(),
                $startDate ?? null,
                $dueDate ?? null,
                (bool) $request->query->get('isActive', true)
            )
        );

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/tasks/{id}", name="get_specific_task", methods={"GET"}, requirements={"id"="\d+"})
     *
     * @param TaskRepository $taskRepository
     * @param TaskPermissionChecker $taskPermissionChecker
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getSpecificTask(
        TaskRepository $taskRepository,
        TaskPermissionChecker $taskPermissionChecker,
        int $id
    ): JsonResponse {
        $task = $taskRepository->find($id);

        if (null === $task) {
            return $this->createNotFoundJsonResponse('The requested task is not found.');
        }

        if (!$taskPermissionChecker->canReadTask($task, $this->getUser())) {
            return $this->createForbiddenJsonResponse('Access denied for the requested task.');
        }

        return new JsonResponse(
            [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus()->getDescription(),
                'startDate' => $task->getStartDate()->format('d-m-Y H:i:s'),
                'dueDate' => $task->getDueDate()->format('d-m-Y H:i:s'),
                'created' => $task->getCreated()->format('d-m-Y H:i:s'),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/tasks", name="create_task", methods={"POST"})
     *
     * @param Request $request
     * @param TaskApiHandler $taskApiHandler
     *
     * @return JsonResponse
     */
    public function create(Request $request, TaskApiHandler $taskApiHandler): JsonResponse
    {
        try {
            $taskApiHandler->createFromApi(\json_decode($request->getContent(), true));
        } catch (MissingMandatoryParameterException|InvalidDateTimeFormatException $e) {
            return $this->createBadRequestJsonResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->createInternalErrorJsonResponse('Task was not created.');
        }

        return new JsonResponse(['message' => 'Task has been successfully created.'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/tasks/{id}", name="task_full_update", methods={"PUT"}, requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param TaskApiHandler $taskApiHandler
     * @param TaskPermissionChecker $taskPermissionChecker
     * @param int $id
     *
     * @return JsonResponse
     */
    public function fullUpdate(
        Request $request,
        TaskApiHandler $taskApiHandler,
        TaskPermissionChecker $taskPermissionChecker,
        int $id
    ): JsonResponse {
        $task = $this->getDoctrine()->getRepository(Task::class)->find($id);

        if (null === $task) {
            return $this->createNotFoundJsonResponse('The requested task is not found.');
        }

        if (!$taskPermissionChecker->canUpdateTask($task, $this->getUser())) {
            return $this->createForbiddenJsonResponse('Access denied for the requested task.');
        }

        try {
            $taskApiHandler->fullUpdateFromApi($task, \json_decode($request->getContent(), true));
        } catch (MissingMandatoryParameterException|InvalidDateTimeFormatException $e) {
            return $this->createBadRequestJsonResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->createInternalErrorJsonResponse('Task was not created.');
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/tasks/{id}", name="task_partial_update", methods={"PATCH"}, requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param TaskApiHandler $taskApiHandler
     * @param TaskPermissionChecker $taskPermissionChecker
     * @param int $id
     *
     * @return JsonResponse
     */
    public function partialUpdate(
        Request $request,
        TaskApiHandler $taskApiHandler,
        TaskPermissionChecker $taskPermissionChecker,
        int $id
    ): JsonResponse {
        $task = $this->getDoctrine()->getRepository(Task::class)->find($id);

        if (null === $task) {
            return $this->createNotFoundJsonResponse('The requested task is not found.');
        }

        if (!$taskPermissionChecker->canUpdateTask($task, $this->getUser())) {
            return $this->createForbiddenJsonResponse('Access denied for the requested task.');
        }

        try {
            $taskApiHandler->partialUpdateFromApi($task, \json_decode($request->getContent(), true));
        } catch (MissingMandatoryParameterException|InvalidDateTimeFormatException $e) {
            return $this->createBadRequestJsonResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->createInternalErrorJsonResponse('Task was not created.');
        }

        return new JsonResponse(
            [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus()->getDescription(),
                'startDate' => $task->getStartDate()->format('d-m-Y H:i:s'),
                'dueDate' => $task->getDueDate()->format('d-m-Y H:i:s'),
                'created' => $task->getCreated()->format('d-m-Y H:i:s'),
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/tasks/{id}", name="delete_task", methods={"DELETE"}, requirements={"id"="\d+"})
     *
     * @param TaskPermissionChecker $taskPermissionChecker
     * @param int $id
     *
     * @return JsonResponse
     */
    public function delete(TaskPermissionChecker $taskPermissionChecker, int $id): JsonResponse
    {
        $task = $this->getDoctrine()->getRepository(Task::class)->find($id);

        if (null === $task) {
            return $this->createNotFoundJsonResponse('The requested task is not found.');
        }

        if (!$taskPermissionChecker->canDeleteTask($this->getUser())) {
            return $this->createForbiddenJsonResponse('Access denied for the requested task.');
        }

        $this->getDoctrine()->getManager()->remove($task);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}

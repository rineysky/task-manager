<?php

declare(strict_types=1);

namespace App\Controller;

use App\Classes\Exceptions\InvalidDateTimeFormatException;
use App\Classes\Exceptions\MissingMandatoryParameterException;
use App\Classes\Helpers\Task\TaskApiHandler;
use App\Classes\Helpers\Task\TaskPermissionChecker;
use App\Entity\Task;
use App\Repository\TaskRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
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
     * @SWG\Response(
     *     response=200,
     *     description="Returns the tasks of an user",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Task::class, groups={"taskList"}))
     *     )
     * )
     * @SWG\Response(
     *     response="500",
     *     description="Returned when a server error occurred"
     * )
     * @SWG\Parameter(
     *     name=Task::START_DATE_API_KEY,
     *     in="query",
     *     type="string",
     *     description="Task start date",
     *     @SWG\Schema(
     *         example="01-04-2020 00:00:00"
     *     )
     * )
     * @SWG\Parameter(
     *     name=Task::DUE_DATE_API_KEY,
     *     in="query",
     *     type="string",
     *     description="Task due date",
     *     @SWG\Schema(
     *         example="01-04-2020 23:59:59"
     *     )
     * )
     * @SWG\Parameter(
     *     name="isActive",
     *     in="query",
     *     type="boolean",
     *     description="Only active tasks"
     * )
     * @SWG\Tag(name="tasks")
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

        $data = $taskRepository->getActiveByUserAndDates(
            $this->getUser(),
            $startDate ?? null,
            $dueDate ?? null,
            (bool) $request->query->get('isActive', true)
        );

        return new JsonResponse($this->serialize($data, ['taskList']), Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/tasks/{id}", name="get_specific_task", methods={"GET"}, requirements={"id"="\d+"})
     * @SWG\Response(
     *     response=200,
     *     description="Returns the specific task details",
     *     @SWG\Schema(ref=@Model(type=Task::class, groups={"taskDetails"}))
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied for the requested task"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="The requested task is not found"
     * )
     * @SWG\Response(
     *     response="500",
     *     description="Returned when a server error occurred"
     * )
     * @SWG\Tag(name="specificTask")
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

        return new JsonResponse($this->serialize($task, ['taskDetails']), Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/tasks", name="create_task", methods={"POST"})
     * @SWG\Response(
     *     response=200,
     *     description="Creates the task"
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Provided task data is invalid"
     * )
     * @SWG\Response(
     *     response="500",
     *     description="Returned when a server error occurred"
     * )
     * @SWG\Parameter(
     *     name=Task::TITLE_API_KEY,
     *     in="body",
     *     required=true,
     *     description="Task title",
     *     @SWG\Schema(type="string")
     * )
     * @SWG\Parameter(
     *     name=Task::DESCRIPTION_API_KEY,
     *     in="body",
     *     required=true,
     *     description="Task description",
     *     @SWG\Schema(type="string")
     * )
     * @SWG\Parameter(
     *     name=Task::START_DATE_API_KEY,
     *     in="body",
     *     required=true,
     *     description="Task start date",
     *     @SWG\Schema(
     *         type="string",
     *         example="01-04-2020 00:00:00"
     *     )
     * )
     * @SWG\Parameter(
     *     name=Task::DUE_DATE_API_KEY,
     *     in="body",
     *     required=true,
     *     description="Task due date",
     *     @SWG\Schema(
     *         type="string",
     *         example="01-04-2020 23:59:59"
     *     )
     * )
     * @SWG\Tag(name="createTask")
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
     * @SWG\Response(
     *     response=204,
     *     description="Full updates the task"
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Provided task data is invalid"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied for the requested task"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="The requested task is not found"
     * )
     * @SWG\Response(
     *     response="500",
     *     description="Returned when a server error occurred"
     * )
     * @SWG\Parameter(
     *     name=Task::TITLE_API_KEY,
     *     in="body",
     *     required=true,
     *     description="Task title",
     *     @SWG\Schema(type="string")
     * )
     * @SWG\Parameter(
     *     name=Task::DESCRIPTION_API_KEY,
     *     in="body",
     *     required=true,
     *     description="Task description",
     *     @SWG\Schema(type="string")
     * )
     * @SWG\Parameter(
     *     name=Task::START_DATE_API_KEY,
     *     in="body",
     *     required=true,
     *     description="Task start date",
     *     @SWG\Schema(
     *         type="string",
     *         example="01-04-2020 00:00:00"
     *     )
     * )
     * @SWG\Parameter(
     *     name=Task::DUE_DATE_API_KEY,
     *     in="body",
     *     required=true,
     *     description="Task due date",
     *     @SWG\Schema(
     *         type="string",
     *         example="01-04-2020 23:59:59"
     *     )
     * )
     * @SWG\Tag(name="fullUpdateTask")
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
     * @SWG\Response(
     *     response=200,
     *     description="Partial updates the task"
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Provided task data is invalid"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied for the requested task"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="The requested task is not found"
     * )
     * @SWG\Response(
     *     response="500",
     *     description="Returned when a server error occurred"
     * )
     * @SWG\Parameter(
     *     name=Task::TITLE_API_KEY,
     *     in="body",
     *     required=false,
     *     description="Task title",
     *     @SWG\Schema(type="string")
     * )
     * @SWG\Parameter(
     *     name=Task::DESCRIPTION_API_KEY,
     *     in="body",
     *     required=false,
     *     description="Task description",
     *     @SWG\Schema(type="string")
     * )
     * @SWG\Parameter(
     *     name=Task::START_DATE_API_KEY,
     *     in="body",
     *     required=false,
     *     description="Task start date",
     *     @SWG\Schema(
     *         type="string",
     *         example="01-04-2020 00:00:00"
     *     )
     * )
     * @SWG\Parameter(
     *     name=Task::DUE_DATE_API_KEY,
     *     in="body",
     *     required=false,
     *     description="Task due date",
     *     @SWG\Schema(
     *         type="string",
     *         example="01-04-2020 23:59:59"
     *     )
     * )
     * @SWG\Tag(name="partialUpdateTask")
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

        return new JsonResponse($this->serialize($task, ['taskDetails']), Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/tasks/{id}", name="delete_task", methods={"DELETE"}, requirements={"id"="\d+"})
     * @SWG\Response(
     *     response=204,
     *     description="Deletes the task"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="Access denied for the requested task"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="The requested task is not found"
     * )
     * @SWG\Response(
     *     response="500",
     *     description="Returned when a server error occurred"
     * )
     * @SWG\Tag(name="deleteTask")
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

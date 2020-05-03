<?php

declare(strict_types=1);

namespace App\Controller;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiController extends AbstractController
{
    /**
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function createNotFoundJsonResponse(string $message): JsonResponse
    {
        return new JsonResponse(
            [
                'message' => $message,
            ],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function createForbiddenJsonResponse(string $message): JsonResponse
    {
        return new JsonResponse(
            [
                'message' => $message,
            ],
            Response::HTTP_FORBIDDEN
        );
    }

    /**
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function createBadRequestJsonResponse(string $message): JsonResponse
    {
        return new JsonResponse(
            [
                'message' => $message,
            ],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function createInternalErrorJsonResponse(string $message): JsonResponse
    {
        return new JsonResponse(
            [
                'message' => \sprintf(
                    '%s Please contact the administrator.',
                    $message
                ),
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * @param mixed $data
     * @param array $serializationGroups
     *
     * @return string
     */
    protected function serialize($data, array $serializationGroups): string
    {
        $serializer = SerializerBuilder::create()->build();
        $serializationContext = SerializationContext::create();
        $serializationContext->setGroups($serializationGroups);

        return $serializer->serialize($data, 'json', $serializationContext);
    }
}

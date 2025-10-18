<?php

namespace App\Controller\Api;

use App\Dto\InspectionListQueryDto;
use App\Service\InspectionService;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InspectionController extends AbstractController
{
    public function __construct(
        private readonly InspectionService $inspectionService,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Get list of inspections with filters and pagination
     */
    #[Route('/api/inspections', name: 'api_inspections_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            // Build query DTO from request
            $queryDto = new InspectionListQueryDto();
            $queryDto->startDate = $request->query->get('startDate');
            $queryDto->endDate = $request->query->get('endDate');
            $queryDto->page = (int) ($request->query->get('page', 1));
            $queryDto->limit = (int) ($request->query->get('limit', 50));
            $queryDto->createdByUserId = $request->query->has('createdByUserId')
                ? (int) $request->query->get('createdByUserId')
                : null;

            // Validate query parameters
            $violations = $this->validator->validate($queryDto);

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }

                $this->logger->warning('Invalid request parameters', [
                    'errors' => $errors,
                    'params' => $request->query->all()
                ]);

                return new JsonResponse([
                    'success' => false,
                    'errors' => $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            // Call service
            $response = $this->inspectionService->getInspections($queryDto);

            // Return JSON response
            return $this->json($response, Response::HTTP_OK);

        } catch (InvalidArgumentException $e) {
            $this->logger->warning('Invalid request parameters', [
                'exception' => $e->getMessage(),
                'params' => $request->query->all()
            ]);

            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);

        } catch (Exception $e) {
            $this->logger->error('Error fetching inspections', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'params' => $request->query->all()
            ]);

            return new JsonResponse([
                'success' => false,
                'error' => 'Wystąpił błąd serwera'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

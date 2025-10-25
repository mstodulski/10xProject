<?php

namespace App\Controller;

use App\Entity\Inspection;
use App\Form\InspectionType;
use App\Repository\InspectionRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MainController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function indexAction(): Response
    {
        return $this->render('Main/index.html.twig',
            [

            ]
        );
    }

    #[Route('/inspections', name: 'api_get_inspections', options: ['expose' => true])]
    public function getInspectionsAction(Request $request, InspectionRepository $inspectionRepository): JsonResponse
    {
        // FullCalendar automatycznie wysyła parametry 'start' i 'end' w formacie ISO 8601
        $startParam = $request->query->get('start');
        $endParam = $request->query->get('end');

        // Walidacja parametrów
        if (!$startParam || !$endParam) {
            return $this->json([
                'error' => 'Brak wymaganych parametrów start i end'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Konwersja do DateTimeImmutable
            $startDate = new DateTimeImmutable($startParam);
            $endDate = new DateTimeImmutable($endParam);
        } catch (Exception $e) {
            return $this->json([
                'error' => 'Nieprawidłowy format daty'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Pobierz oględziny z zakresu dat
        $inspections = $inspectionRepository->findByDateRange($startDate, $endDate);

        // Formatuj do formatu FullCalendar
        $events = [];
        foreach ($inspections as $inspection) {
            $events[] = [
                'id' => $inspection->getId(),
                'title' => sprintf(
                    '%s - %s %s (%s)',
                    $inspection->getClientName(),
                    $inspection->getVehicleMake(),
                    $inspection->getVehicleModel(),
                    $inspection->getLicensePlate()
                ),
                'start' => $inspection->getStartDatetime()->format('c'), // ISO 8601
                'end' => $inspection->getEndDatetime()->format('c'),
                // Dodatkowe dane dostępne w extendedProps
                'extendedProps' => [
                    'clientName' => $inspection->getClientName(),
                    'phoneNumber' => $inspection->getPhoneNumber(),
                    'vehicleMake' => $inspection->getVehicleMake(),
                    'vehicleModel' => $inspection->getVehicleModel(),
                    'licensePlate' => $inspection->getLicensePlate(),
                    'createdBy' => $inspection->getCreatedByUser()->getName(),
                ]
            ];
        }

        return $this->json($events);
    }

    /**
     * Render inspection form
     */
    #[Route('/inspection/form', name: 'inspection_form', methods: ['GET'])]
    public function inspectionFormAction(Request $request): Response
    {
        // Get datetime from query parameter if provided (for calendar slot click)
        $datetime = $request->query->get('datetime');

        $inspection = new Inspection();
        $startDatetime = null;

        // If datetime provided, parse it
        if ($datetime) {
            try {
                $startDatetime = new DateTimeImmutable($datetime);
            } catch (Exception $e) {
                // If invalid datetime, just leave empty
            }
        }

        $form = $this->createForm(InspectionType::class, $inspection);

        // Set data for unmapped fields - DateType and TimeType expect DateTimeInterface objects
        if ($startDatetime) {
            $form->get('startDate')->setData($startDatetime);
            $form->get('startTime')->setData($startDatetime);
        }

        return $this->render('Main/_inspection_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Create new inspection
     */
    #[Route('/inspection/create', name: 'inspection_create', methods: ['POST'])]
    public function createInspectionAction(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ): JsonResponse {
        // Check if user is logged in
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Musisz być zalogowany aby utworzyć oględziny'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $inspection = new Inspection();

        // Set the user who created the inspection BEFORE validation
        $inspection->setCreatedByUser($user);

        $form = $this->createForm(InspectionType::class, $inspection);

        $inspectionData = $request->request->all('inspection');
        $startDateString = $inspectionData['startDate'] ?? null;
        $startTimeString = $inspectionData['startTime'] ?? null;

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->json([
                'success' => false,
                'message' => 'Formularz nie został wysłany'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$form->isValid()) {
            // Collect form errors
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            return $this->json([
                'success' => false,
                'message' => 'Formularz zawiera błędy',
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Get date and time from request (fields are unmapped)
            $inspectionData = $request->request->all('inspection');
            $startDateString = $inspectionData['startDate'] ?? null;
            $startTimeString = $inspectionData['startTime'] ?? null;

            if (!$startDateString || !$startTimeString) {
                return $this->json([
                    'success' => false,
                    'message' => 'Data i godzina rozpoczęcia są wymagane',
                    'errors' => ['Data i godzina rozpoczęcia są wymagane']
                ], Response::HTTP_BAD_REQUEST);
            }

            // Create DateTimeImmutable from date and time strings
            $startDatetimeString = $startDateString . ' ' . $startTimeString;
            $startDatetime = new DateTimeImmutable($startDatetimeString);
            $inspection->setStartDatetime($startDatetime);

            // Calculate end datetime (30 minutes after start)
            $endDatetime = $startDatetime->modify('+30 minutes');
            $inspection->setEndDatetime($endDatetime);

            // Validate the entity
            $violations = $validator->validate($inspection);

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => 'Dane oględzin są nieprawidłowe',
                    'errors' => $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            // Save to database
            $entityManager->persist($inspection);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => $translator->trans('inspection.inspection_created'),
                'inspection' => [
                    'id' => $inspection->getId(),
                    'startDatetime' => $inspection->getStartDatetime()->format('c'),
                    'endDatetime' => $inspection->getEndDatetime()->format('c'),
                ]
            ], Response::HTTP_CREATED);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $translator->trans('inspection.error_creating_inspection'),
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

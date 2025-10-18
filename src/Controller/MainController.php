<?php

namespace App\Controller;

use App\Repository\InspectionRepository;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

//    #[Route('/api/inspections', name: 'api_get_inspections', options: ['expose' => true])]
//    public function getInspectionsAction(Request $request, InspectionRepository $inspectionRepository): JsonResponse
//    {
//        // FullCalendar automatycznie wysyła parametry 'start' i 'end' w formacie ISO 8601
//        $startParam = $request->query->get('start');
//        $endParam = $request->query->get('end');
//
//        // Walidacja parametrów
//        if (!$startParam || !$endParam) {
//            return $this->json([
//                'error' => 'Brak wymaganych parametrów start i end'
//            ], Response::HTTP_BAD_REQUEST);
//        }
//
//        try {
//            // Konwersja do DateTimeImmutable
//            $startDate = new DateTimeImmutable($startParam);
//            $endDate = new DateTimeImmutable($endParam);
//        } catch (Exception $e) {
//            return $this->json([
//                'error' => 'Nieprawidłowy format daty'
//            ], Response::HTTP_BAD_REQUEST);
//        }
//
//        // Pobierz oględziny z zakresu dat
//        $inspections = $inspectionRepository->findByDateRange($startDate, $endDate);
//
//        // Formatuj do formatu FullCalendar
//        $events = [];
//        foreach ($inspections as $inspection) {
//            $events[] = [
//                'id' => $inspection->getId(),
//                'title' => sprintf(
//                    '%s - %s %s (%s)',
//                    $inspection->getClientName(),
//                    $inspection->getVehicleMake(),
//                    $inspection->getVehicleModel(),
//                    $inspection->getLicensePlate()
//                ),
//                'start' => $inspection->getStartDatetime()->format('c'), // ISO 8601
//                'end' => $inspection->getEndDatetime()->format('c'),
//                // Dodatkowe dane dostępne w extendedProps
//                'extendedProps' => [
//                    'clientName' => $inspection->getClientName(),
//                    'phoneNumber' => $inspection->getPhoneNumber(),
//                    'vehicleMake' => $inspection->getVehicleMake(),
//                    'vehicleModel' => $inspection->getVehicleModel(),
//                    'licensePlate' => $inspection->getLicensePlate(),
//                    'createdBy' => $inspection->getCreatedByUser()->getName(),
//                ]
//            ];
//        }
//
//        return $this->json($events);
//    }
}

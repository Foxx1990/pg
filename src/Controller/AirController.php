<?php

namespace App\Controller;

use App\Controller\Service\AirApiService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AirController
 * @package App\Controller
 */
class AirController extends AbstractController
{
    /** @var AirApiService */
    private $airApiService;

    /**
     * AirController constructor.
     * @param AirApiService $airQualityService
     */
    public function __construct(AirApiService $airApiService)
    {
        $this->airApiService = $airApiService;
    }

    /**
     * @Route("/", name="air_quality.index")
     *
     * @return Response
     * @throws Exception
     */
    public function index(): Response
    {
        try {
            $stations = $this->airApiService->getAll();
        } catch (Exception $e) {
            $msg = 'Nie udało się pobrać stacji. Treść błędu: ';
            throw new Exception($msg.$e->getMessage());
        }

        return $this->render('air_quality/index.html.twig', [
            'stations' => $stations,
        ]);
    }

    /**
     * @Route("/station", name="air_quality.station_sensors")
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     */
    public function showStationSensors(Request $request): Response
    {
        try {
            $sensors = $this->airApiService->getStationSensors($request->get('idStation'));
        } catch (Exception $e) {
            $msg = 'Nie udało się pobrać czujników dla danej stacji. Treść błędu: ';
            throw new Exception($msg.$e->getMessage());
        }

        try {
            $airQuality = $this->airApiService->getAirQualityForStation($request->get('idStation'));
        } catch (Exception $e) {
            $msg = 'Nie udało się pobrać wyników jakości powietrza dla danej stacji. Treść błędu: ';
            throw new Exception($msg.$e->getMessage());
        }

        $sensorValuesNames = $this->airApiService->matchSensorsWithData($sensors, $airQuality);

        $airQualityBoostrapClass = $this->airApiService->getBootstrapClassByStIndex($airQuality['stIndexLevel']['id']);

        return $this->render('air_quality/station_sensors.html.twig', [
            'sensors' => $sensors,
            'airQuality' => $airQuality,
            'airQualityBoostrapClass' => $airQualityBoostrapClass,
            'sensorValuesNames' => $sensorValuesNames,
        ]);
    }
}

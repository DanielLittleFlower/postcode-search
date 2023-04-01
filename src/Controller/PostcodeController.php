<?php
namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostcodeController extends AbstractController
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Searches for postcodes that start with a given partial code.
     *
     * @Route("/postcodes/search/{query}", name="search_postcodes")
     *
     * @param Request $request The HTTP request.
     * @param Connection $connection The database connection.
     *
     * @return JsonResponse The JSON response containing matching postcodes.
     */
    public function postcodeSearchAction(Request $request, Connection $connection): JsonResponse
    {
        $partial = $request->query->get('partial');
        $limit = $request->query->get('limit', 10);
        $statement = $connection->prepare('SELECT * FROM postcodes WHERE postcode LIKE :partial LIMIT :limit');
        $statement->bindValue('partial', $partial . '%');
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $result = $statement->executeQuery();
        $results = $result->fetchAllAssociative();

        $data = array_map(function ($result) {
            return [
                'type' => 'postcode',
                'id' => $result['postcode'],
                'attributes' => [
                    'latitude' => (float) $result['latitude'],
                    'longitude' => (float) $result['longitude']
                ]
            ];
        }, $results);

        return new JsonResponse(['data' => $data]);
    }
    
    /**
     * Searches for postcodes within a certain distance from a given location.
     *
     * @Route("/postcode/nearby/{latitude}/{longitude}", name="postcode_nearby")
     *
     * @param Request $request The HTTP request.
     * @param float $latitude The latitude of the location.
     * @param float $longitude The longitude of the location.
     *
     * @return JsonResponse The JSON response containing nearby postcodes.
     */
    public function nearby(Request $request, float $latitude, float $longitude): JsonResponse
    {
        $distance = $request->query->get('distance', 1.0);
        $limit = $request->query->get('limit', 10);

        $statement = $this->connection->prepare('SELECT postcode, latitude, longitude, ( 3959 * acos( cos( radians(:latitude) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(:longitude) ) + sin( radians(:latitude) ) * sin( radians( latitude ) ) ) ) AS distance FROM postcodes HAVING distance < :distance ORDER BY distance LIMIT :limit');
        $statement->bindValue('latitude', $latitude);
        $statement->bindValue('longitude', $longitude);
        $statement->bindValue('distance', $distance);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $result = $statement->executeQuery();
        $results = $result->fetchAllAssociative();

        $data = array_map(function ($result) {
            return [
                'type' => 'postcode',
                'id' => $result['postcode'],
                'attributes' => [
                    'latitude' => (float) $result['latitude'],
                    'longitude' => (float) $result['longitude']
                ]
            ];
        }, $results);

        return new JsonResponse(['data' => $data]);
    }

}

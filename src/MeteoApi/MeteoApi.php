<?php 
namespace App\MeteoApi;
use GuzzleHttp\Client;
use JMS\Serializer\SerializerInterface;
use App\Constant\NumberConstant;

class MeteoApi
{
    private $weatherClient;
    private $serializer;
    private $apiKey;

    public function __construct(Client $weatherClient, SerializerInterface $serializer)
    {
        $this->apiKey           = NumberConstant::KEY_API_METEO;
        $this->serializer       = $serializer;
        $this->weatherClient    = $weatherClient;
    }

    public function getCurrent($local = 'Antananarivo')
    {
       #http://openweathermap.org/img/w/01d.png
       $uri = '/data/2.5/weather?q='.$local.'&APPID='.$this->apiKey;
        
        try {
            $response = $this->weatherClient->get($uri);
        } catch (\Exception $e) {
            // Penser à logger l'erreur.            
            return ['error' => 'Les informations ne sont pas disponibles pour le moment.'];
        }
        

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        return [$data];
    }
}

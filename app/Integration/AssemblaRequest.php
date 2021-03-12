<?php

namespace App\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Query;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AssemblaRequest extends Model
{
    /**
     * default per_page field value used on the application
     */
    const PER_PAGE = 100;

    /**
     * Assembla API URL used to shorten endpoints
     */
    const ASSEMBLA_API_URL = 'https://api.assembla.com/v1/';

    /**
     * This function is used for the GET request to the Assembla API
     *
     * @param string $endpoint
     * @param string $applicationKey Key for Assembla Authorization
     * @param string $applicationSecret Secret for Assembla Authorization
     * @param array $queryParams
     *
     * @param bool  $multiple this parameter triggers logic to enable query string requests (to be able to have i.e multiple productIds)
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function get($endpoint, $applicationKey, $applicationSecret, $queryParams = [], $multiple = false)
    {
        $requestData = [
            'headers' => [
                'X-Api-Key'    => $applicationKey,
                'X-Api-Secret' => Crypt::decrypt($applicationSecret),
            ],
            'allow_redirects' => [
                'max'             => 10,        // allow at most 10 redirects.
                'strict'          => true,      // use "strict" RFC compliant redirects.
                'referer'         => true,      // add a Referer header
                'protocols'       => ['https'], // only allow https URLs
                // 'on_redirect'     => $onRedirect,//callback for a redirect, not used
                'track_redirects' => true
            ],
            'query' => [
                'page' => 1,
                'per_page' => self::PER_PAGE,
            ]
        ];
        $requestData['query'] = array_merge($requestData['query'], $queryParams);

        if ($multiple) {
            $requestData['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
            $queryString = Query::build($requestData['query']);
            $requestData['body'] = $queryString ;
            unset($requestData['query']);
        }

        $client = new Client();
        return $client->request('GET', self::ASSEMBLA_API_URL.$endpoint, $requestData);//GuzzleHttp\Exception\ConnectException
    }

    /**
     * This function is used for POST requests to the Assembla API
     * currently used only when tracking time
     *
     * @param      $endpoint
     * @param      $params
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function post($endpoint, $applicationKey, $applicationSecret, $params)
    {
        $requestData = [
            'headers' => [
                'X-Api-Key'    => $applicationKey,
                'X-Api-Secret' => Crypt::decrypt($applicationSecret),
            ],
            'form_params' => $params,
        ];

        $client = new Client();
        return $client->post(self::ASSEMBLA_API_URL.$endpoint, $requestData);
    }

    /**
     * This function is used for PUT requests to the Assembla API
     * currently used only for:
     * - Updating a Milestone as closed
     * - Updating a ticket milestone
     *
     * @param      $endpoint
     * @param      $params
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function put($endpoint, $applicationKey, $applicationSecret, $params)
    {
        $requestData = [
            'headers' => [
                'X-Api-Key'    => $applicationKey,
                'X-Api-Secret' => Crypt::decrypt($applicationSecret),
            ],
            'form_params' => $params,
        ];

        /** @var \GuzzleHttp\Client $client */
        $client = new Client();
        return $client->put(self::ASSEMBLA_API_URL.$endpoint, $requestData);
    }


}

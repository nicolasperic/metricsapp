<?php

namespace App\Integration;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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
     * This function is used for the GET action on the Assembla API
     *
     * @param       $endpoint
     * @param array $queryParams
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function get($endpoint, $applicationKey, $applicationSecret, $queryParams = [])
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

        $client = new Client();
        return $client->request('GET', self::ASSEMBLA_API_URL.$endpoint, $requestData);//GuzzleHttp\Exception\ConnectException
    }

    /**
     * Alternative GET action sending query data as query string
     * This had to be done to get multiple tasks for different ticket ids using the following query structure:
     * ?ticket_ids[]=ticket_id&ticket_ids[]=another_ticket_id...
     * TODO merge get and getMultiple functions into one with a parameter to alternate between query string on body or query param
     *
     * @param       $endpoint
     * @param array $queryParams
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function getMultiple($endpoint, $applicationKey, $applicationSecret, $queryParams = [])
    {
        $requestData = [
            'headers' => [
                'X-Api-Key'    => $applicationKey,
                'X-Api-Secret' => Crypt::decrypt($applicationSecret),
                'Content-Type' => 'application/x-www-form-urlencoded'//this is different on the get function
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
        //differences with the get function
        $query_string = \GuzzleHttp\Psr7\build_query($requestData['query'], PHP_QUERY_RFC1738);
        unset($requestData['query']);
        $requestData['body'] = $query_string ;
        //print print_r($requestData['query'],1);
        print $query_string.PHP_EOL;
        //differences end here > also see Content-Type on headers



        $client = new Client();
        return $client->request('GET', self::ASSEMBLA_API_URL.$endpoint, $requestData);
    }

    /**
     * This function is used for POST to the Assembla API
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


}

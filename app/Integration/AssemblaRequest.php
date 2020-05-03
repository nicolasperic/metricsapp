<?php

namespace App\Integration;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;

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
    //TODO generate configuration page to allow customer to set the key and secret
    const APPLICATION_KEY = 'a5aa5632989ec768d71d';
    const APPLICATION_SECRET = '497e452c605c29f8971aeb367e6c15a872749efe';

    /**
     * This function is used for the GET action on the Assembla API
     *
     * @param       $url
     * @param array $queryParams
     * @param bool  $assemblaPrefix
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function get($url, $queryParams = [], $assemblaPrefix = true)
    {
        if ($assemblaPrefix)
            $url = self::ASSEMBLA_API_URL.$url;

        $requestData = [
            'headers' => [
                'X-Api-Key'    => self::APPLICATION_KEY,
                'X-Api-Secret' => self::APPLICATION_SECRET
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
        return $client->request('GET', $url, $requestData);
    }

    /**
     * This function is used for POST to the Assembla API
     * currently used only when tracking time
     *
     * @param      $url
     * @param      $params
     * @param bool $assemblaPrefix
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function post($url, $params, $assemblaPrefix = true)
    {
        if ($assemblaPrefix)
            $url = self::ASSEMBLA_API_URL.$url;

        $requestData = [
            'headers' => [
                'X-Api-Key'    => self::APPLICATION_KEY,
                'X-Api-Secret' => self::APPLICATION_SECRET
            ],
            'form_params' => $params,
        ];

        $client = new Client();

        return $client->post($url, $requestData);
    }
}

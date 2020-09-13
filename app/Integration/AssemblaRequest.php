<?php

namespace App\Integration;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
    public static function get($endpoint, $queryParams = [])
    {
        $requestData = [
            'headers' => [
                'X-Api-Key'    => self::getApplicationKey(),
                'X-Api-Secret' => self::getApplicationSecret(),
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
    public static function post($endpoint, $params)
    {
        $requestData = [
            'headers' => [
                'X-Api-Key'    => self::getApplicationKey(),
                'X-Api-Secret' => self::getApplicationSecret(),
            ],
            'form_params' => $params,
        ];

        $client = new Client();
        return $client->post(self::ASSEMBLA_API_URL.$endpoint, $requestData);
    }

    /**
     * This function will return the application key from the logged in user
     * https://app.assembla.com/user/edit/manage_clients
     *
     * @return mixed
     */
    private static function getApplicationKey()
    {
        if (Auth::check()) {
            return Auth::user()->assembla_key;
        }
    }

    /**
     * This function will return the application secret from the logged in user
     * https://app.assembla.com/user/edit/manage_clients
     *
     * @return mixed
     */
    private static function getApplicationSecret()
    {
        if (Auth::check()) {
            return Auth::user()->assembla_secret;
        }
    }

}

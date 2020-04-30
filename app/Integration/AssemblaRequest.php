<?php

namespace App\Integration;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;

class AssemblaRequest extends Model
{
    const ASSEMBLA_API_URL = 'https://api.assembla.com/v1/';
    const APPLICATION_KEY = 'a5aa5632989ec768d71d';
    const APPLICATION_SECRET = '497e452c605c29f8971aeb367e6c15a872749efe';

    /**
     * @param string $url
     * @param bool $assemblaPrefix
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function get($url, $page = 1, $assemblaPrefix = true)
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
            //TODO validate how to handle query params and iterate a collection with multiple pages
            'query' => [
                'page' => $page,
                'per_page' => 100,
                'ticket_status' => 'all',
                'sort_by' => 'id',
                'sort_order' => 'desc',
            ]
        ];

        $client = new Client();
        return $client->request('GET', $url, $requestData);
    }

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

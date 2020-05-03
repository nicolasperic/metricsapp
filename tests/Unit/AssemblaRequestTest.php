<?php

namespace Tests\Unit;

use App\Integration\AssemblaRequest;
use Tests\TestCase;

class AssemblaRequestTest extends TestCase
{
    /** @test */
    function can_replace_query_params()
    {
        $requestData = [
            'headers' => [
                'X-Api-Key'    => 'APP_KEY',
                'X-Api-Secret' => 'APP_SECRET'
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
                'per_page' => AssemblaRequest::PER_PAGE,
                'ticket_status' => 'all',
                'sort_by' => 'id',
                'sort_order' => 'desc',
            ]
        ];

        $queryParams = [
            'page' => 2,
            'per_page' => 25,
        ];

        $requestData['query'] = array_merge($requestData['query'], $queryParams);

        $this->assertEquals(2, $requestData['query']['page']);
        $this->assertEquals(25, $requestData['query']['per_page']);
        $this->assertEquals('APP_KEY', $requestData['headers']['X-Api-Key']);
    }
}

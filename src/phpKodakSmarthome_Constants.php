<?php
namespace phpKodakSmarthome;

class phpKodakSmarthome_Constants {

    public const DEVICE_EVENT_MOTION = 1;
    public const DEVICE_EVENT_SOUND = 2;
    public const DEVICE_EVENT_BATTERY = 7;

    public const EVENT_DESCRIPTION = [
        1 => 'Motion detected',
        2 => 'Sound detected',
        7 => 'Battery critical'
    ];

    # REGION_URLS
    public const SUPPORTED_REGIONS = [
        'EU' => [
            'URL' => 'https://app-eu.kodaksmarthome.com/web',
            'URL_TOKEN' => 'https://api-t01-r3.perimetersafe.com/v1/oauth/token',
            'URL_AUTH' => 'https://app-eu.kodaksmarthome.com/web/authenticate',
            'URL_DEVICES' => 'https://app-eu.kodaksmarthome.com/web/user/device',
            'URL_LOGOUT' => 'https://app-eu.kodaksmarthome.com/web/#/user/logout',
            'URL_EVENTS' => 'https://app-eu.kodaksmarthome.com/web/user/device/event',
        ]
    ];


    # HTTP_CLIENT
    public const HTTP_CLIENT_MODEL = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.79 Safari/537.36';
    public const HTTP_CLIENT_AUTHORIZATION = 'Basic MjFmOTk1M2VlZGE4N2I3MGRjMTE1ZTUyNDU2ODE1OWNjNmExNzI2MTNiOGUyMGMwMTUzMGZjNjg2ODc3Mzk2ZDo0ZDA5YmZlMWRhMjU0YmRjNzA4YjEzMGIxMzVmYzA2NjU4ODI2MWZjNTY2YWQzMWEyMGM1YjA5ZTY3NTFkNTgy';


    # HTTP_HEADERS
    public const HTTP_HEADERS_BASIC = [
        'Accept' => 'application/json, text/plain, */*',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Accept-Language' => 'en-GB,en-US;q=0.9,en;q=0.8',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
        'Origin' => 'https://app-eu.kodaksmarthome.com',
        'Referer' => 'https://app-eu.kodaksmarthome.com/web/',
        'Sec-Fetch-Site' => 'cross-site',
        'Sec-Fetch-Mode' => 'cors',
        'User-Agent' => self::HTTP_CLIENT_MODEL,
    ];

    public const HTTP_HEADERS_AUTH = [
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Authorization' => self::HTTP_CLIENT_AUTHORIZATION,
    ];

    public const HTTP_HEADERS_OPTIONS = [
        'Access-Control-Request-Method' => 'POST',
        'Access-Control-Request-Headers' => 'authorization',
        'Authorization' => 'None',
    ];
}
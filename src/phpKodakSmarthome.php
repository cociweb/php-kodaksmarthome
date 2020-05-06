<?php
namespace phpKodakSmarthome;

use DateTime;
use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;
use InvalidArgumentException;
use phpKodakSmarthome\phpKodakSmarthome_Constants as CONSTANTS;
use stdClass;

/**
 * Class phpKodakSmarthome
 *
 * @package phpKodakSmarthome
 */
class phpKodakSmarthome {

    /**
     * @var string
     */
    private string $username;
    /**
     * @var string
     */
    private string $password;
    /**
     * @var string|null
     */
    private ?string $token = null;
    /**
     * @var string|null
     */
    private ?string $cookie = null;
    /**
     * @var string|null
     */
    private ?string $userId = null;
    /**
     * @var array
     */
    private array $tokenInfo = [];
    /**
     * @var stdClass
     */
    private stdClass $accountInfo;
    /**
     * @var stdClass
     */
    private stdClass $webUrls;
    /**
     * @var array
     */
    private ?array $devices = null;
    /**
     * @var array
     */
    private ?array $events = null;
    /**
     * @var array|string[]
     */
    private array $regionUrl;


    /**
     * phpKodakSmarthome constructor.
     *
     * @param string $username
     * @param string $password
     * @param string $region
     * @throws InvalidArgumentException
     */
    public function __construct(string $username, string $password, string $region = 'EU') {

        $this->username = $username;
        $this->password = $password;

        if (!array_key_exists($region, CONSTANTS::SUPPORTED_REGIONS)) {
            throw new InvalidArgumentException($region . ' is not supported!');
        }
        $this->regionUrl = CONSTANTS::SUPPORTED_REGIONS[$region];
    }

    /**
     * @return bool
     * @throws ConnectionErrorException
     */
    public function getToken() : bool {

        $payload = 'grant_type=password' .
            '&username=' . $this->username .
            '&password=' . $this->password .
            '&model=' . CONSTANTS::HTTP_CLIENT_MODEL;

        $return = Request::post($this->regionUrl['URL_TOKEN'])
            ->addHeaders(array_merge(CONSTANTS::HTTP_HEADERS_BASIC, CONSTANTS::HTTP_HEADERS_AUTH))
            ->body($payload)
            ->send();

        if ($return->code === 200) {
            $body = $return->body;
            $this->tokenInfo = [
                'access_token' => $body->access_token,
                'token_type' => $body->token_type,
                'refresh_token' => $body->refresh_token,
                'expires_in' => $body->expires_in,
                'scope' => $body->scope,
            ];
            $this->accountInfo = $body->account_info;
            $this->webUrls = $body->web_urls;
            $this->token = $this->tokenInfo['access_token'];
            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws ConnectionErrorException
     */
    public function authenticate() : bool {

        if (!$this->token) {
            $this->getToken();
        }

        $payload = 'username=&password=' .
            $this->token .
            '&rememberme=false';

        $return = Request::post($this->regionUrl['URL_AUTH'])
            ->addHeaders(array_merge(CONSTANTS::HTTP_HEADERS_BASIC, CONSTANTS::HTTP_HEADERS_AUTH))
            ->body($payload)
            ->send();

        if ($return->code === 200) {
            $this->userId = $return->body->data->id;
            $headers = $return->headers->toArray();
            $cookie = explode(';', $headers['Set-Cookie']);
            $this->cookie = str_replace('JSESSIONID=', '', $cookie[0]);
            return true;
        }

        return false;
    }

    /**
     * @return array|null
     * @throws ConnectionErrorException
     */
    public function getDevices() : ?array {

        if (!$this->userId) {
            $this->authenticate();
        }

        $payload = '?access_token=' . $this->token;

        $return = Request::get($this->regionUrl['URL_DEVICES'] . $payload)
            ->addHeaders(array_merge(CONSTANTS::HTTP_HEADERS_BASIC, CONSTANTS::HTTP_HEADERS_AUTH))
            ->addHeader('Cookie', 'JSESSIONID=' . $this->cookie)
            ->send();

        if ($return->code === 200) {
            $this->devices = $return->body->data;
        }
        return $this->devices;
    }

    /**
     * @return array|null
     * @throws ConnectionErrorException
     */
    public function getEvents() : ?array {

        if (!$this->devices) {
            $this->getDevices();
        }

        foreach ($this->devices as $device) {
            $deviceId = $device->device_id;
            $pages = 1;
            $event_pages = 1;

            while ($pages <= $event_pages) {
                $payload = '?access_token=' . $this->token .
                    '&device_id=' . $deviceId .
                    '&page=' . $pages;

                $return = Request::get($this->regionUrl['URL_EVENTS'] . $payload)
                    ->addHeaders(array_merge(CONSTANTS::HTTP_HEADERS_BASIC, CONSTANTS::HTTP_HEADERS_AUTH))
                    ->addHeader('Cookie', 'JSESSIONID=' . $this->cookie)
                    ->send();

                if ($return->code === 200) {

                    $data = $return->body->data;

                    $event_pages = $data->total_pages;

                    if ($data->total_events === 0) {
                        continue;
                    }

                    foreach ($data->events as $event) {
                        if (!isset($this->events[$event->id])) {
                            $date = new DateTime($event->created_date);
                            $preview = null;
                            $video = null;
                            if ($event->event_type === CONSTANTS::DEVICE_EVENT_MOTION) {
                                $preview = $event->snapshot;
                                $video = $event->data[0]->file;
                            }
                            $this->events[$event->id] = [
                                'timestamp' => $date->format('Y-m-d H:i:s'),
                                'type' => CONSTANTS::EVENT_DESCRIPTION[$event->event_type],
                                'preview' => $preview,
                                'video' => $video,
                                'original' => json_encode($event),
                            ];
                        }
                    }

                }

                $pages++;
            }
        }
        return $this->events;

    }

}
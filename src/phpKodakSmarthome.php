<?php
namespace phpKodakSmarthome;

use Httpful\Request;
use phpKodakSmarthome\phpKodakSmarthome_Constants as CONSTANTS;

class phpKodakSmarthome {

    private string $username;
    private string $password;
    private ?string $token = null;
    private ?string $cookie = null;
    private ?string $userId = null;
    private array $tokenInfo = [];
    private $accountInfo;
    private $webUrls;
    private array $devices = [];
    private array $events = [];
    private array $regionUrl = [];
    private bool $isConnected = false;

    public function __construct(string $username, string $password, string $region='EU') {

        $this->username = $username;
        $this->password = $password;

        if (!array_key_exists($region, CONSTANTS::SUPPORTED_REGIONS)) {
            throw new \Exception($region . ' i not supported!');
        }
        $this->regionUrl = CONSTANTS::SUPPORTED_REGIONS[$region];
    }

    public function getToken() : bool {
        $payload = 'grant_type=password&username=' . $this->username . '&password=' . $this->password . '&model=' . CONSTANTS::HTTP_CLIENT_MODEL;
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

    public function authenticate() : bool {
        if (!$this->token) {
            $this->getToken();
        }
        $payload = 'username=&password=' . $this->token . '&rememberme=false';
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

    public function getDevices() : bool {
        if (!$this->userId) {
            $this->authenticate();
        }
        $payload = '?access_token=' . $this->token;
        $return = Request::get($this->regionUrl['URL_DEVICES'] . $payload)
            ->addHeaders(array_merge(CONSTANTS::HTTP_HEADERS_BASIC, CONSTANTS::HTTP_HEADERS_AUTH))
            ->send();
        var_dump($return);
        return true;
    }

}
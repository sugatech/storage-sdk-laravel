<?php

namespace Storage\SDK;

use PassportClientCredentials\OAuthClient;
use Zttp\PendingZttpRequest;
use Zttp\Zttp;
use Zttp\ZttpResponse;

class StorageClient
{
    /**
     * @var OAuthClient
     */
    private $oauthClient;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @param string $apiUrl
     */
    public function __construct($apiUrl)
    {
        $this->oauthClient = new OAuthClient(
            config('storage.oauth.url'),
            config('storage.oauth.client_id'),
            config('storage.oauth.client_secret')
        );
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param callable $handler
     * @return ZttpResponse
     */
    private function request($handler)
    {
        $request = Zttp::withHeaders([
            'Authorization' => 'Bearer ' . $this->oauthClient->getAccessToken(),
        ])
            ->withoutVerifying();

        $response = $handler($request);

        if ($response->status() == 401) {
            $this->oauthClient->getAccessToken(true);
        }

        return $response;
    }

    /**
     * @param string $route
     * @return string
     */
    private function getUrl($route)
    {
        return $this->apiUrl . '/api/client/v1' . $route;
    }

    /**
     * @param string $file
     * @param string $path
     * @return array
     */
    public function createFile($file, $path)
    {
        $f = fopen($file, 'r');

        $params = [
            [
                'name' => 'path',
                'contents' => $path,
            ],
            [
                'name' => 'file',
                'contents' => $f,
            ]
        ];

        $response = $this->request(function (PendingZttpRequest $request) use ($params) {
            return $request->asMultipart()
                ->post($this->getUrl('/files'), $params);
        });

        fclose($f);

        return $response->json();
    }

    /**
     * @param int|string $id
     * @return bool
     */
    public function deleteFile($id)
    {
        $normalizeId = is_numeric($id) ? $id : base64_encode($id);

        return $this->request(function (PendingZttpRequest $request) use ($normalizeId) {
            return $request->delete($this->getUrl('/files/' . $normalizeId));
        })
            ->isSuccess();
    }

    /**
     * @param int|string $id
     * @return array
     */
    public function getFile($id)
    {
        $normalizeId = is_numeric($id) ? $id : base64_encode($id);

        return $this->request(function (PendingZttpRequest $request) use ($normalizeId) {
            return $request->get($this->getUrl('/files/' . $normalizeId));
        })
            ->json();
    }

    /**
     * @param array $params
     * @return array[]
     */
    public function getFiles($params = [])
    {
        return $this->request(function (PendingZttpRequest $request) use ($params) {
            return $request->get($this->getUrl('/files'), $params);
        })
            ->json();
    }
}
<?php

namespace Storage\SDK;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use OAuth2ClientCredentials\OAuthClient;

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
     * @return Response
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function request($handler)
    {
        $request = Http::withHeaders([
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
     * @param string $filePath
     * @param string $path
     * @param string $fileName
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function createFile($filePath, $path, $fileName = null)
    {
        $f = fopen($filePath, 'r');

        $params = [
            [
                'name' => 'path',
                'contents' => $path,
            ],
            [
                'name' => 'file',
                'contents' => $f,
                'filename' => $fileName,
            ]
        ];

        $response = $this->request(function (PendingRequest $request) use ($params) {
            return $request->asMultipart()
                ->post($this->getUrl('/files'), $params);
        });

        fclose($f);

        return $response->json();
    }

    /**
     * @param int|string $idOrPath
     * @return bool
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function deleteFile($idOrPath)
    {
        $idOrBase64Path = is_numeric($idOrPath) ? $idOrPath : base64_encode($idOrPath);

        return $this->request(function (PendingRequest $request) use ($idOrBase64Path) {
            return $request->delete($this->getUrl('/files/' . $idOrBase64Path));
        })
            ->successful();
    }

    /**
     * @param int|string $idOrPath
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getFile($idOrPath)
    {
        $idOrBase64Path = is_numeric($idOrPath) ? $idOrPath : base64_encode($idOrPath);

        return $this->request(function (PendingRequest $request) use ($idOrBase64Path) {
            return $request->get($this->getUrl('/files/' . $idOrBase64Path));
        })
            ->json();
    }

    /**
     * @param array $params
     * @return array[]
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getFiles($params = [])
    {
        return $this->request(function (PendingRequest $request) use ($params) {
            return $request->get($this->getUrl('/files'), $params);
        })
            ->json();
    }

    /**
     * @param int|string $idOrPath
     * @param int $expiresIn
     * @param array $options
     * @param boolean $fallbackToUrl
     * @return string
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getTemporaryUrl($idOrPath, $expiresIn, $options = [], $fallbackToUrl = false)
    {
        $idOrBase64Path = is_numeric($idOrPath) ? $idOrPath : base64_encode($idOrPath);

        $params = [
            'expires_in' => $expiresIn,
            'options' => $options,
            'fallback_to_url' => $fallbackToUrl,
        ];

        return $this->request(function (PendingRequest $request) use ($idOrBase64Path, $params) {
            return $request->get($this->getUrl('/files/' . $idOrBase64Path . '/temporary-url'), $params);
        })
            ->json();
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function deleteMultiple($ids = [])
    {
        return $this->request(function (PendingRequest $request) use ($ids) {
            return $request->delete($this->getUrl('/files'), ['ids' => $ids]);
        })
            ->json();
    }
}
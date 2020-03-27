<?php

namespace Storage\SDK;

use Zttp\PendingZttpRequest;
use Zttp\Zttp;

class StorageClient
{
    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @param string $apiUrl
     * @param string $accessToken
     */
    public function __construct($apiUrl, $accessToken)
    {
        $this->apiUrl = $apiUrl;
        $this->accessToken = $accessToken;
    }

    /**
     * @return PendingZttpRequest
     */
    private function request()
    {
        return Zttp::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])
            ->withoutVerifying();
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
     * @return object
     */
    public function createFile($file, $path)
    {
        $f = fopen($file, 'r');

        $response = $this->request()
            ->asMultipart()
            ->post(
                $this->getUrl('/files'),
                [
                    [
                        'name' => 'path',
                        'contents' => $path,
                    ],
                    [
                        'name' => 'file',
                        'contents' => $f,
                    ]
                ]
            );

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

        return $this->request()
            ->delete($this->getUrl('/files/' . $normalizeId))
            ->isSuccess();
    }

    /**
     * @param int|string $id
     * @return array
     */
    public function getFile($id)
    {
        $normalizeId = is_numeric($id) ? $id : base64_encode($id);

        return $this->request()
            ->get($this->getUrl('/files/' . $normalizeId))
            ->json();
    }

    /**
     * @param array $params
     * @return object[]
     */
    public function getFiles($params = [])
    {
        return $this->request()
            ->get($this->getUrl('/files'), $params)
            ->json();
    }
}
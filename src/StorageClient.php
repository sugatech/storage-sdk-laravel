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
        return Zttp::withOptions([
            'base_uri' => $this->apiUrl . '/api/client/v1',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ],
            'verify' => false,
        ]);
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
                '/files',
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

        return $response->body();
    }

    /**
     * @param int|string $id
     * @return bool
     */
    public function deleteFile($id)
    {
        $normalizeId = is_numeric($id) ? $id : base64_encode($id);

        return $this->request()
            ->delete('/files/' . $normalizeId)
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
            ->get('/files/' . $normalizeId)
            ->body();
    }

    /**
     * @param array $params
     * @return object[]
     */
    public function getFiles($params)
    {
        return $this->request()
            ->get('/files')
            ->body();
    }
}
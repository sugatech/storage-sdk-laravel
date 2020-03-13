<?php

namespace Storage\SDK;

use Zttp\Zttp;

class StorageClient
{
    const CLIENT_PATH = '/api/client/v1/file';

    /**
     * @var Zttp
     */
    private $client;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * StorageClient constructor.
     * @param Zttp $client
     * @param string $apiUrl
     * @param string $accessToken
     */
    public function __construct($client, $apiUrl, $accessToken)
    {
        $this->client = $client;
        $this->accessToken = $accessToken;
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param string $file
     * @param string $path
     * @return mixed
     */
    public function createFile($file, $path)
    {
        $f = fopen($file, 'r');

        $response = $this->client::asMultipart()->withHeaders([
            'Authorization' => $this->accessToken,
        ])->post($this->apiUrl.self::CLIENT_PATH, [
            [
                'name' => 'path',
                'contents' => $path,
            ],
            [
                'name' => 'file',
                'contents' => $f,
            ]
        ]);

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

        $response = $this->client::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => $this->accessToken,
        ])->delete($this->apiUrl.self::CLIENT_PATH.'/'.$normalizeId);

        return $response->isSuccess();
    }

    /**
     * @param int|string $id
     * @return object
     */
    public function getFile($id)
    {
        $normalizeId = is_numeric($id) ? $id : base64_encode($id);

        $response = $this->client::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => $this->accessToken,
        ])->get($this->apiUrl.self::CLIENT_PATH.'/'.$normalizeId);

        return $response->body();
    }

    /**
     * @param array $params
     * @return object[]
     */
    public function getListFiles($params)
    {
        $response = $this->client::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => $this->accessToken,
        ])->get($this->apiUrl.self::CLIENT_PATH, $params);

        return $response->body();
    }
}
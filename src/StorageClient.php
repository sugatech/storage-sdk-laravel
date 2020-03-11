<?php

namespace Storage\SDK;

use Zttp\Zttp;

class StorageClient
{
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
        ])->post($this->apiUrl.'/api/client/v1/file', [
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
     * @return mixed
     */
    public function deleteFile($id)
    {
        $normalizeId = is_numeric($id) ? $id : base64_encode($id);

        $response = $this->client::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => $this->accessToken,
        ])->delete($this->apiUrl.'/api/client/v1/file/'.$normalizeId);

        return $response->body();
    }

    /**
     * @param int|string $id
     * @return mixed
     */
    public function getFile($id)
    {
        $normalizeId = is_numeric($id) ? $id : base64_encode($id);

        $response = $this->client::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => $this->accessToken,
        ])->get($this->apiUrl.'/api/client/v1/file/'.$normalizeId);

        return $response->body();
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function getListFiles($params)
    {
        $response = $this->client::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => $this->accessToken,
        ])->get($this->apiUrl.'/api/client/v1/file', $params);

        return $response->body();
    }
}
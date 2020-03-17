<?php

namespace Storage\SDK;

use Zttp\Zttp;

class StorageClient
{
    const CLIENT_PATH = '/api/client/v1/file';

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $root;

    /**
     * StorageClient constructor.
     * @param string $apiUrl
     * @param string $accessToken
     */
    public function __construct($apiUrl, $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->root = $apiUrl.self::CLIENT_PATH;
    }

    /**
     * @param string $file
     * @param string $path
     * @return object
     */
    public function createFile($file, $path)
    {
        $f = fopen($file, 'r');

        $response = Zttp::asMultipart()->withHeaders([
            'Authorization' => $this->accessToken,
        ])->post($this->root, [
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

        $response = Zttp::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => $this->accessToken,
        ])->delete($this->root.'/'.$normalizeId);

        return $response->isSuccess();
    }

    /**
     * @param int|string $id
     * @return object
     */
    public function getFile($id)
    {
        $normalizeId = is_numeric($id) ? $id : base64_encode($id);

        $response = Zttp::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => $this->accessToken,
        ])->get($this->root.'/'.$normalizeId);

        return $response->body();
    }

    /**
     * @param array $params
     * @return object[]
     */
    public function getFiles($params)
    {
        $response = Zttp::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => $this->accessToken,
        ])->get($this->root, $params);

        return $response->body();
    }
}
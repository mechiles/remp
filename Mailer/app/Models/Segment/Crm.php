<?php
declare(strict_types=1);

namespace Remp\MailerModule\Models\Segment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\StreamInterface;

class Crm implements ISegment
{
    const PROVIDER_ALIAS = 'crm-segment';

    const ENDPOINT_LIST = 'api/v1/user-segments/list';

    const ENDPOINT_USERS = 'api/v1/user-segments/users';

    private $baseUrl;

    private $token;

    public function __construct(string $baseUrl, string $token)
    {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
    }

    public function provider(): string
    {
        return static::PROVIDER_ALIAS;
    }

    public function list(): array
    {
        $response = $this->request(static::ENDPOINT_LIST);

        try {
            $stream = \GuzzleHttp\Psr7\StreamWrapper::getResource($response);
            $segments = [];
            foreach (\JsonMachine\JsonMachine::fromStream($stream, "/segments") as $segment) {
                $segments[] = [
                    'name' => $segment['name'],
                    'provider' => static::PROVIDER_ALIAS,
                    'code' => $segment['code'],
                    'group' => $segment['group'],
                ];
            }
        } finally {
            fclose($stream);
        }

        return $segments;
    }

    public function users(array $segment): array
    {
        $response = $this->request(static::ENDPOINT_USERS, ['code' => $segment['code']]);

        try {
            $stream = \GuzzleHttp\Psr7\StreamWrapper::getResource($response);
            $userIds = [];
            foreach (\JsonMachine\JsonMachine::fromStream($stream, "/users") as $user) {
                $userIds[] = $user['id'];
            }
        } finally {
            fclose($stream);
        }

        return $userIds;
    }

    private function request(string $url, array $query = []): StreamInterface
    {
        $client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ]
        ]);

        try {
            $response = $client->get($url, [
                'query' => $query,
            ]);

            return $response->getBody();
        } catch (ConnectException $connectException) {
            throw new SegmentException("Could not connect to Segment:{$url} endpoint: {$connectException->getMessage()}");
        }
    }
}

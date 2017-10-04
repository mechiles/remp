<?php

namespace App\Contracts\Crm;

use App\CampaignSegment;
use App\Contracts\SegmentContract;
use App\Contracts\SegmentException;
use App\Jobs\CacheSegmentJob;
use Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Collection;
use Razorpay\BloomFilter\Bloom;

class Segment implements SegmentContract
{
    const PROVIDER_ALIAS = 'crm_segment';

    const ENDPOINT_LIST = 'user-segments/list';

    const ENDPOINT_CHECK = 'user-segments/check';

    const ENDPOINT_USERS = 'user-segments/users';

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function provider(): string
    {
        return self::PROVIDER_ALIAS;
    }

    /**
     * @return Collection
     * @throws SegmentException
     */
    public function list(): Collection
    {
        try {
            $response = $this->client->get(self::ENDPOINT_LIST);
        } catch (ConnectException $e) {
            throw new SegmentException("Could not connect to Segment:List endpoint: {$e->getMessage()}");
        }

        $list = json_decode($response->getBody());
        $campaignSegments = [];
        foreach ($list->segments as $item) {
            $cs = new CampaignSegment();
            $cs->name = $item->name;
            $cs->provider = self::PROVIDER_ALIAS;
            $cs->code = $item->code;
            $cs->group = $item->group;
            $campaignSegments[] = $cs;
        }
        $collection = collect($campaignSegments);
        return $collection;
    }

    /**
     * @param CampaignSegment $campaignSegment
     * @param $userId
     * @return bool
     * @throws SegmentException
     */
    public function check(CampaignSegment $campaignSegment, $userId): bool
    {
        $bloomFilter = Cache::tags([SegmentContract::BLOOM_FILTER_CACHE_TAG])->get($campaignSegment->code);
        if (!$bloomFilter) {
            dispatch(new CacheSegmentJob($campaignSegment));

            try {
                $response = $this->client->get(self::ENDPOINT_CHECK, [
                    'query' => [
                        'resolver_type' => 'id',
                        'resolver_value' => $userId,
                        'code' => $campaignSegment->code,
                    ],
                ]);
            } catch (ConnectException $e) {
                throw new SegmentException("Could not connect to Segment:Check endpoint: {$e->getMessage()}");
            }

            $result = json_decode($response->getBody());
            return $result->check;
        }

        /** @var Bloom $bloomFilter */
        $bloomFilter = unserialize($bloomFilter);
        return $bloomFilter->has($userId);
    }

    /**
     * @param CampaignSegment $campaignSegment
     * @return Collection
     * @throws SegmentException
     */
    public function users(CampaignSegment $campaignSegment): Collection
    {
        try {
            $response = $this->client->get(self::ENDPOINT_USERS, [
                'query' => [
                    'code' => $campaignSegment->code,
                ],
            ]);
        } catch (ConnectException $e) {
            throw new SegmentException("Could not connect to Segment:Check endpoint: {$e->getMessage()}");
        }

        $list = json_decode($response->getBody());
        $collection = collect($list->users);
        return $collection;
    }
}

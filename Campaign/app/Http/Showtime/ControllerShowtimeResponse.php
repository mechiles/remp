<?php

namespace App\Http\Showtime;

use App\Banner;
use App\Campaign;
use App\CampaignBanner;
use View;

class ControllerShowtimeResponse implements ShowtimeResponse
{
    public function error($callback, int $statusCode, array $errors)
    {
        return response()
            ->jsonp($callback, [
                'success' => false,
                'errors' => $errors,
            ]);
    }

    public function success(string $callback, $data, $activeCampaignIds, $providerData)
    {
        return response()
            ->jsonp($callback, [
                'success' => true,
                'errors' => [],
                'data' => $data,
                'activeCampaignIds' => $activeCampaignIds,
                'providerData' => $providerData,
            ]);
    }


    public function renderBanner(Banner $banner, array $alignments, array $dimensions, array $positions): string
    {
        return View::make('banners.preview', [
            'banner' => $banner,
            'variantUuid' => '',
            'campaignUuid' => '',
            'positions' => $positions,
            'dimensions' => $dimensions,
            'alignments' => $alignments,
            'controlGroup' => 0
        ])->render();
    }

    public function renderCampaign(
        CampaignBanner $variant,
        Campaign $campaign,
        array $alignments,
        array $dimensions,
        array $positions
    ): string {
        return View::make('banners.preview', [
            'banner' => $variant->banner,
            'variantUuid' => $variant->uuid,
            'campaignUuid' => $campaign->uuid,
            'positions' => $positions,
            'dimensions' => $dimensions,
            'alignments' => $alignments,
            'controlGroup' => $variant->control_group
        ])->render();
    }
}

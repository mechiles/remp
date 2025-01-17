<?php

namespace App\Console\Commands;

use App\Banner;
use App\Contracts\SegmentAggregator;
use App\Variable;
use Illuminate\Console\Command;
use App\Campaign;

class CampaignsRefreshCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:refresh-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes cached campaigns and banners and cache the latest version immediately.';

    /**
     * @var SegmentAggregator
     */
    private $segmentAggregator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SegmentAggregator $segmentAggregator)
    {
        parent::__construct();
        $this->segmentAggregator = $segmentAggregator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // serialize segment aggregator (for showtime.php)
        $this->segmentAggregator->serializeToRedis();

        $activeCampaignIds = Campaign::refreshActiveCampaignsCache();

        foreach (Campaign::whereIn('id', $activeCampaignIds)->get() as $campaign) {
            $this->line(sprintf('Refreshing campaign: <info>%s</info>', $campaign->name));
            $campaign->cache();
        };

        foreach (Banner::all() as $banner) {
            $this->line(sprintf('Refreshing banner: <info>%s</info>', $banner->name));
            $banner->cache();
        }

        Variable::refreshVariableCache();

        $this->line('Campaigns cache refreshed.');
        return 0;
    }
}

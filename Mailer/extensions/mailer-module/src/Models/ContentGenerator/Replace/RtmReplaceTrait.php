<?php
declare(strict_types=1);

namespace Remp\MailerModule\Models\ContentGenerator\Replace;

use Remp\MailerModule\Models\ContentGenerator\GeneratorInput;

trait RtmReplaceTrait
{
    /**
     * Put RTM parameters into URL parameters
     * Function also respects MailGun Variables (e.g. %recipient.autologin%)
     *
     * @param string $hrefUrl
     *
     * @param GeneratorInput $generatorInput
     * @return string
     */
    public function replaceUrl(string $hrefUrl, GeneratorInput $generatorInput): string
    {
        $utmSource = $generatorInput->template()->mail_type->code; // !! could be maybe performance issue?
        $utmMedium = 'email';
        $utmCampaign = $generatorInput->template()->code;
        $utmContent = $generatorInput->batchId();

        $matches = [];
        // Split URL between path and params (before and after '?')
        preg_match('/^([^?]*)\??(.*)$/', $hrefUrl, $matches);

        $path = $matches[1];
        $params = explode('&', $matches[2] ?? '');
        $finalParams = [];

        $rtmSourceAdded = $rtmMediumAdded = $rtmCampaignAdded = $rtmContentAdded = false;


        foreach ($params as $param) {
            if (empty($param)) {
                continue;
            }

            $items = explode('=', $param, 2);

            if (isset($items[1])) {
                $key = $items[0];
                $value = $items[1];

                if (strcasecmp($key, 'rtm_source') === 0) {
                    $finalParams[] = "$key={$utmSource}";
                    $rtmSourceAdded = true;
                } elseif (strcasecmp($key, 'rtm_medium') === 0) {
                    $finalParams[] = "$key={$utmMedium}";
                    $rtmMediumAdded = true;
                } elseif (strcasecmp($key, 'rtm_campaign') === 0) {
                    $finalParams[] = "$key={$utmCampaign}";
                    $rtmCampaignAdded = true;
                } elseif (strcasecmp($key, 'rtm_content') === 0) {
                    $finalParams[] = "$key={$utmContent}";
                    $rtmContentAdded = true;
                } else {
                    $finalParams[] = "$key=" . $value;
                }
            } else {
                $finalParams[] = $param;
            }
        }

        if (!$rtmSourceAdded) {
            $finalParams[] = "rtm_source={$utmSource}";
        }
        if (!$rtmMediumAdded) {
            $finalParams[] = "rtm_medium={$utmMedium}";
        }
        if (!$rtmCampaignAdded) {
            $finalParams[] = "rtm_campaign={$utmCampaign}";
        }
        if (!$rtmContentAdded) {
            $finalParams[] = "rtm_content={$utmContent}";
        }

        return $path . '?' . implode('&', $finalParams);
    }

    public function replace(string $content, GeneratorInput $generatorInput): string
    {
        $matches = [];
        preg_match_all('/<a(.*?)href="([^"]*?)"(.*?)>/i', $content, $matches);

        if (count($matches) > 0) {
            foreach ($matches[2] as $idx => $hrefUrl) {
                if (strpos($hrefUrl, 'http') === false) {
                    continue;
                }

                $href = sprintf(
                    '<a%shref="%s"%s>',
                    $matches[1][$idx],
                    $this->replaceUrl($hrefUrl, $generatorInput),
                    $matches[3][$idx],
                );
                $content = str_replace($matches[0][$idx], $href, $content);
            }
        }

        return $content;
    }
}

<?php
declare(strict_types=1);

namespace Remp\Mailer\Models\Generators;

use Remp\MailerModule\Models\ContentGenerator\Engine\EngineFactory;
use Remp\MailerModule\Models\ContentGenerator\Engine\IEngine;
use Remp\MailerModule\Models\ContentGenerator\Engine\TwigEngine;

class WordpressBlockParser
{
    public const BLOCK_CORE_GROUP = 'core/group';
    public const BLOCK_CORE_HEADING = 'core/heading';
    public const BLOCK_CORE_PARAGRAPH = 'core/paragraph';
    public const BLOCK_CORE_LIST = 'core/list';
    public const BLOCK_CORE_COLUMN = 'core/column';
    public const BLOCK_CORE_COLUMNS = 'core/columns';
    public const BLOCK_CORE_IMAGE = 'core/image';
    public const BLOCK_CORE_LIST_ITEM = 'core/list-item';
    public const BLOCK_CORE_EMBED = 'core/embed';

    public const BLOCK_DN_MINUTE = 'dn-newsletter/r5m-minute';

    /** @var TwigEngine $twig */
    private IEngine $twig;

    private int $minuteOrderCounter = 1;

    private bool $isFirstDNMinuteInGroup = true;

    public function __construct(
        EngineFactory $engineFactory,
        private EmbedParser $embedParser
    ) {
        $this->twig = $engineFactory->engine('twig');
    }

    public function parseJson(string $json)
    {
        $data = preg_replace('/[[:cntrl:]]/', '', $json);
        $data = json_decode($data);

        $result = '';
        $textResult = '';
        foreach ($data as $block) {
            $result .= $this->parseBlock($block);
            $textResult .= strip_tags($result);
        }

        return [$result, $textResult];
    }

    public function getBlockTemplateData(object $block): array
    {
        $data = [
            'originalContent' => $block->originalContent ?? null,
            'content' => $block->attributes->content ?? null,
            'verticalAlignment' => $block->attributes->verticalAlignment ?? null,
            'fontSize' => $block->attributes->style->typography->fontSize ?? null,
            'width' => $block->attributes->width ?? null,
            'url' => $block->attributes->url ?? null,
            'alt' => $block->attributes->alt ?? null,
            'caption' => $block->attributes->caption ?? null,
            'href' => $block->attributes->href ?? null,
        ];

        if ($block->name === self::BLOCK_CORE_GROUP
            && isset($block->attributes->className)
            && str_contains($block->attributes->className, 'wp-block-dn-newsletter-group-grey')
        ) {
            $data['group_grey'] = true;
        }

        if ($block->name === self::BLOCK_CORE_LIST) {
            $data['list_type'] = str_contains($data['originalContent'], 'ol') !== false ? 'ol' : 'ul';
        }

        if ($block->name === self::BLOCK_CORE_GROUP
            && isset($block->attributes->className)
            && str_contains($block->attributes->className, 'wp-block-dn-newsletter-group-ordered')
        ) {
            $data['group_ordered'] = true;
        }

        if ($block->name === self::BLOCK_CORE_EMBED) {
            $data['embed_html'] = $this->embedParser->parse($block->attributes->url);
        }

        if ($block->name === self::BLOCK_DN_MINUTE) {
            $data['isFirstDNMinuteInGroup'] = $this->isFirstDNMinuteInGroup;
        }
        return $data;
    }

    public function parseBlock(object $block, bool $groupOrdered = false): string
    {
        $params = [
            'contents' => ''
        ];

        $params += $this->getBlockTemplateData($block);

        if ($block->name === self::BLOCK_CORE_GROUP) {
            $this->minuteOrderCounter = 1;
            $this->isFirstDNMinuteInGroup = true;
        } elseif ($block->name === self::BLOCK_CORE_HEADING) {
            $this->isFirstDNMinuteInGroup = true;
        } elseif ($block->name === self::BLOCK_DN_MINUTE) {
            $this->isFirstDNMinuteInGroup = false;
        }

        if ($groupOrdered && $block->name === self::BLOCK_DN_MINUTE && $this->minuteOrderCounter < 6) {
            $params['minuteOrderCounter'] = $this->minuteOrderCounter;
            $this->minuteOrderCounter++;
        }

        $template = $this->getTemplate($block->name);
        if (isset($block->innerBlocks) && !empty($block->innerBlocks)) {
            foreach ($block->innerBlocks as $innerBlock) {
                $params['contents'] .= $this->parseBlock($innerBlock, $params['group_ordered'] ?? false);
            }
        }
        return $this->twig->render($template, $params);
    }

    public function getTemplate(string $blockName): string
    {
        $templateFile = match ($blockName) {
            self::BLOCK_CORE_GROUP => __DIR__ . '/resources/templates/WordpressBlockParser/core-group.twig',
            self::BLOCK_CORE_PARAGRAPH => __DIR__ . '/resources/templates/WordpressBlockParser/core-paragraph.twig',
            self::BLOCK_CORE_HEADING => __DIR__ . '/resources/templates/WordpressBlockParser/core-heading.twig',
            self::BLOCK_CORE_IMAGE => __DIR__ . '/resources/templates/WordpressBlockParser/core-image.twig',
            self::BLOCK_CORE_COLUMN => __DIR__ . '/resources/templates/WordpressBlockParser/core-column.twig',
            self::BLOCK_CORE_COLUMNS => __DIR__ . '/resources/templates/WordpressBlockParser/core-columns.twig',
            self::BLOCK_CORE_LIST => __DIR__ . '/resources/templates/WordpressBlockParser/core-list.twig',
            self::BLOCK_CORE_LIST_ITEM => __DIR__ . '/resources/templates/WordpressBlockParser/core-list-item.twig',
            self::BLOCK_CORE_EMBED => __DIR__ . '/resources/templates/WordpressBlockParser/core-embed.twig',
            self::BLOCK_DN_MINUTE => __DIR__ . '/resources/templates/WordpressBlockParser/dn-minute.twig',
            default => throw new \Exception('not existing block template'),
        };

        return file_get_contents($templateFile);
    }
}

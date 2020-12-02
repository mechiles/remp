<?php
declare(strict_types=1);

namespace Remp\MailerModule\Api\v1\Handlers\Mailers;

use Remp\MailerModule\Repository\BatchesRepository;
use Remp\MailerModule\Repository\JobsRepository;
use Remp\MailerModule\Repository\TemplatesRepository;
use Remp\MailerModule\Segment\Aggregator;
use Tomaj\NetteApi\Handlers\BaseHandler;
use Tomaj\NetteApi\Params\InputParam;
use Tomaj\NetteApi\Params\PostInputParam;
use Tomaj\NetteApi\Response\JsonApiResponse;
use Tomaj\NetteApi\Response\ResponseInterface;

class MailJobCreateApiHandler extends BaseHandler
{
    private $jobsRepository;

    private $batchesRepository;

    private $templatesRepository;

    private $aggregator;

    public function __construct(
        JobsRepository $jobsRepository,
        BatchesRepository $batchesRepository,
        TemplatesRepository $templatesRepository,
        Aggregator $aggregator
    ) {
        parent::__construct();
        $this->jobsRepository = $jobsRepository;
        $this->batchesRepository = $batchesRepository;
        $this->templatesRepository = $templatesRepository;
        $this->aggregator = $aggregator;
    }

    public function params(): array
    {
        return [
            (new PostInputParam('segment_code'))->isRequired(),
            (new PostInputParam('segment_provider'))->isRequired(),
            (new PostInputParam('template_id'))->isRequired(),
        ];
    }

    public function handle(array $params): ResponseInterface
    {
        $templateId = $params['template_id'];
        $template = $this->templatesRepository->find($templateId);
        if (!$template) {
            return new JsonApiResponse(400, ['status' => 'error', 'message' => 'No such template with id:' . $template->id]);
        }

        $segmentCode = $params['segment_code'];
        $segmentProvider = $params['segment_provider'];
        $segmentFound = false;
        foreach ($this->aggregator->list() as $segment) {
            if ($segmentCode === $segment['code'] && $segmentProvider === $segment['provider']) {
                $segmentFound = true;
                break;
            }
        }
        if (!$segmentFound) {
            return new JsonApiResponse(400, ['status' => 'error', 'message' => 'No such segment was found']);
        }

        $mailJob = $this->jobsRepository->add($segmentCode, $segmentProvider);
        $batch = $this->batchesRepository->add($mailJob->id, null, null, BatchesRepository::METHOD_RANDOM);
        $this->batchesRepository->addTemplate($batch, $template);
        $this->batchesRepository->update($batch, ['status' => BatchesRepository::STATUS_READY]);

        return new JsonApiResponse(200, ['status' => 'ok', 'id' => $mailJob->id]);
    }
}

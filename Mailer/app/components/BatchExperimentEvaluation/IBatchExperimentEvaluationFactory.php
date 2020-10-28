<?php
declare(strict_types=1);

namespace Remp\MailerModule\Components;

interface IBatchExperimentEvaluationFactory
{
    /** @return BatchExperimentEvaluation */
    public function create(): BatchExperimentEvaluation;
}

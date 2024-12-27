<?php

namespace Zhortein\ElasticEntityBundle\Metrics;

class QueryMetrics
{
    private float $executionTime;
    private int $totalResults;

    public function __construct(float $executionTime, int $totalResults)
    {
        $this->executionTime = $executionTime;
        $this->totalResults = $totalResults;
    }

    public function getExecutionTime(): float
    {
        return $this->executionTime;
    }

    public function getTotalResults(): int
    {
        return $this->totalResults;
    }
}

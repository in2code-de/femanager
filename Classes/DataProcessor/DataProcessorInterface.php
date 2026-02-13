<?php

declare(strict_types=1);

namespace In2code\Femanager\DataProcessor;

interface DataProcessorInterface
{
    /**
     * @deprecated function signature will chance in V14
     */
    public function initializeDataProcessor();

    public function process(): void;
}

<?php

declare(strict_types=1);
namespace In2code\Femanager\DataProcessor;

/**
 * Interface DataProcessorInterface
 */
interface DataProcessorInterface
{
    public function initializeDataProcessor();

    /**
     * @return void
     */
    public function process(): void;
}

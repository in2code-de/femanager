<?php
declare(strict_types = 1);
namespace In2code\Femanager\DataProcessor;

/**
 * Interface DataProcessorInterface
 */
interface DataProcessorInterface
{
    public function initializeDataProcessor();

    /**
     * @param array $arguments
     * @return array
     */
    public function process(array $arguments): array;
}

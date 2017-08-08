<?php
declare(strict_types=1);
namespace In2code\Femanager\DataProcessor;

/**
 * Class CleanUserGroup to clean empty usergroup arguments
 */
class CleanUserGroup extends AbstractDataProcessor
{

    /**
     * @param array $arguments
     * @return array
     */
    public function process(array $arguments): array
    {
        if (empty($arguments['user']['usergroup'][0]) && empty($arguments['user']['usergroup'][0]['__identity'])) {
            unset($arguments['user']['usergroup'][0]);
        }
        return $arguments;
    }
}

<?php
declare(strict_types=1);
namespace In2code\Femanager\Tests\Unit\Fixture\Utility;

use In2code\Femanager\Utility\BackendUtility as BackendUtilityFemanager;

/**
 * Class BackendUtility
 */
class BackendUtility extends BackendUtilityFemanager
{

    /**
     * @return array
     */
    public static function getCurrentParametersPublic()
    {
        return self::getCurrentParameters();
    }
}

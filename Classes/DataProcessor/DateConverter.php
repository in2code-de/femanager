<?php
declare(strict_types=1);
namespace In2code\Femanager\DataProcessor;

use In2code\Femanager\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;

/**
 * Class DateConverter
 */
class DateConverter extends AbstractDataProcessor
{

    /**
     * @param array $arguments
     * @return array
     */
    public function process(array $arguments): array
    {
        if (!empty($this->controllerArguments['user'])) {
            foreach (GeneralUtility::trimExplode(',', $this->getConfiguration('fieldNames'), true) as $fieldName) {
                $this->controllerArguments['user']
                    ->getPropertyMappingConfiguration()
                    ->forProperty($fieldName)
                    ->setTypeConverterOption(
                        DateTimeConverter::class,
                        DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                        LocalizationUtility::translate('tx_femanager_domain_model_user.dateFormat')
                    );
            }
        }
        return $arguments;
    }
}

<?php
declare(strict_types=1);
namespace In2code\Femanager\Signal;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Trait SignalTrait
 */
trait SignalTrait
{
    /**
     * @var bool
     */
    protected $signalEnabled = true;

    /**
     * Instance a new signalSlotDispatcher and offer a signal
     *
     * @param string $signalClassName
     * @param string $signalName
     * @param array $arguments
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function signalDispatch($signalClassName, $signalName, array $arguments)
    {
        if ($this->isSignalEnabled()) {
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            /** @var Dispatcher $signalSlotDispatcher */
            $signalSlotDispatcher = $objectManager->get(Dispatcher::class);
            return $signalSlotDispatcher->dispatch($signalClassName, $signalName, $arguments);
        }
    }

    /**
     * @return boolean
     */
    protected function isSignalEnabled()
    {
        return $this->signalEnabled;
    }

    /**
     * Signal can be disabled for testing
     *
     * @return void
     */
    protected function disableSignals()
    {
        $this->signalEnabled = false;
    }
}

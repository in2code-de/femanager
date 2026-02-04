<?php

declare(strict_types=1);

namespace In2code\Femanager\Finisher;

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SendParametersFinisher extends AbstractFinisher implements FinisherInterface
{
    public function initializeFinisher(): void
    {
        $this->contentObject->start($this->user->_getProperties());
        $this->finisherConfiguration =  $this->typoScriptService->convertPlainArrayToTypoScriptArray(
            $this->typoScriptSettings['new']['sendPost'] ?? []
        );
    }

    /**
     * Send values via curl to a third party software
     */
    public function sendFinisher(): void
    {
        if ($this->isEnabled()) {
            parse_str($this->getData(), $parsedParams);
            GeneralUtility::makeInstance(RequestFactory::class)
                ->request($this->getTargetUrl(), 'POST', ['form_params' => $parsedParams]);
        }
    }

    /**
     * Get parameters
     */
    protected function getData(): string
    {
        return $this->contentObject->cObjGetSingle(
            (string)$this->finisherConfiguration['data'],
            (array)$this->finisherConfiguration['data.']
        );
    }

    protected function getTargetUrl(): string
    {
        $linkConfiguration = [
            'parameter' => $this->finisherConfiguration['targetUrl'],
            'forceAbsoluteUrl' => '1',
            'returnLast' => 'url',
        ];
        return $this->contentObject->typoLink('dummy', $linkConfiguration);
    }

    protected function isEnabled(): bool
    {
        return $this->contentObject->cObjGetSingle(
            $this->finisherConfiguration['_enable'] ?? 'TEXT',
            $this->finisherConfiguration['_enable.'] ?? '0'
        ) === '1';
    }
}

<?php

declare(strict_types=1);

namespace In2code\Femanager\Finisher;

interface FinisherInterface
{
    public function getUser();

    /**
     * @deprecated function signature will chance in V14
     */
    public function setUser($user);

    /**
     * @deprecated function name will chance in V14 to getTypoScriptSettings()
     */
    public function getSettings();

    /**
     * @deprecated function name and signature will chance in V14 to setTypoScriptSettings(array $typoScriptSettings): FinisherInterface;
     */
    public function setSettings($settings);

    /**
     * @deprecated function signature will chance in V14
     */
    public function getActionMethodName();

    /**
     * @deprecated function signature will chance in V14
     */
    public function setActionMethodName(string $actionMethodName);

    /**
     * @deprecated function signature will chance in V14
     */
    public function initializeFinisher();
}

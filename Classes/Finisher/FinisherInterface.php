<?php

declare(strict_types=1);

namespace In2code\Femanager\Finisher;

interface FinisherInterface
{
    public function getUser();

    public function setUser($user);

    public function getSettings();

    public function setSettings($settings);

    public function getActionMethodName();

    public function setActionMethodName(string $actionMethodName);

    public function initializeFinisher();
}

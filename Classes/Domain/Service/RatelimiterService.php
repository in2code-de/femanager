<?php

declare(strict_types = 1);

namespace In2code\Femanager\Domain\Service;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class RatelimiterService implements SingletonInterface
{
    const CACHE_IDENTIFIER = 'femanager_ratelimiter';
    const DEFAULT_CONFIG = ['timeframe' => 60, 'limit' => 3];
    const LIMIT_IP = 'limit_ip_';
    const SESSION_KEY = 'tx_femanager_ratelimiter';

    /** @var FrontendInterface */
    protected $cache;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $timeframe;

    public function __construct()
    {
        $this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache(self::CACHE_IDENTIFIER);
        $setup = $this->getTSFE()->tmpl->setup;
        $config = $setup['plugin.']['tx_femanager.']['settings.']['ratelimiter.'] ?? self::DEFAULT_CONFIG;
        $this->timeframe = (int)$config['timeframe'];
        $this->limit = (int)$config['limit'];
    }

    protected function getTSFE(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    public function isLimited(): bool
    {
        if ($this->limit > 0) {
            $userIp = GeneralUtility::getIndpEnv('REMOTE_ADDR');
            $userIpAccess = $this->getToken(self::LIMIT_IP, $userIp);

            return count($userIpAccess) >= $this->limit;
        }
        return false;
    }

    public function consumeSlot()
    {
        if ($this->limit > 0) {
            $userIp = GeneralUtility::getIndpEnv('REMOTE_ADDR');
            $this->consumeToken(self::LIMIT_IP, $userIp);
        }
    }

    protected function getCookie()
    {
        $this->touchCookie();

        return $this->getTSFE()->fe_user->getSessionData(self::SESSION_KEY);
    }

    public function touchCookie()
    {
        $feUser = $this->getTSFE()->fe_user;
        $identifier = $feUser->getSessionData(self::SESSION_KEY);

        if (null === $identifier) {
            $unique = GeneralUtility::makeInstance(Random::class)->generateRandomHexString(16);
            $feUser->setAndSaveSessionData(self::SESSION_KEY, $unique);
        }
    }

    protected function consumeToken(string $tokenName, string $value): array
    {
        $cacheID = $this->getCacheID($tokenName, $value);

        $token = $this->retrieveToken($cacheID);
        $token[] = $GLOBALS['EXEC_TIME'];
        $this->cache->set($cacheID, $token, [], $this->timeframe);

        return $token;
    }

    protected function getCacheID(string $tokenName, string $value): string
    {
        return $tokenName . hash('sha1', $value);
    }

    protected function retrieveToken(string $cacheID): array
    {
        $token = [];
        if ($this->cache->has($cacheID)) {
            $token = $this->cache->get($cacheID);
            $token = $this->filterExpiredToken($token);
        }

        return $token;
    }

    protected function filterExpiredToken(array $token): array
    {
        $slidingWindowStartTime = $GLOBALS['EXEC_TIME'] - $this->timeframe;
        foreach ($token as $idx => $accessTime) {
            if ($accessTime < $slidingWindowStartTime) {
                unset($token[$idx]);
            }
        }

        return $token;
    }

    protected function getToken(string $tokenName, string $value): array
    {
        $cacheID = $this->getCacheID($tokenName, $value);

        return $this->retrieveToken($cacheID);
    }
}

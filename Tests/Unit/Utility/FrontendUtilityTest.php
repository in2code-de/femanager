<?php

namespace In2code\Femanager\Tests\Unit\Utility;

use GuzzleHttp\Psr7\ServerRequest;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Tests\Helper\TestingHelper;
use In2code\Femanager\Utility\FrontendUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class FrontendUtilityTest
 * @coversDefaultClass \In2code\Femanager\Utility\FrontendUtility
 */
class FrontendUtilityTest extends UnitTestCase
{
    protected array $testFilesToDelete = [];

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    /**
     * @covers ::forceValue
     */
    public function testForceValue(): void
    {
        $user = new User();

        $properties = [];
        $properties['gender'] = 2;
        $properties['first_name'] = 'Kaspar';
        $properties['tx_extbase_type'] = 'Tx_Extbase_Domain_Model_FrontendUser';

        foreach ($properties as $field => $value) {
            // set value
            FrontendUtility::forceValue($user, $field, $value);
        }

        self::assertSame(2, $user->getGender());
        self::assertSame('Kaspar', $user->getFirstName());
        self::assertSame('Tx_Extbase_Domain_Model_FrontendUser', $user->getTxExtbaseType());
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getControllerName
     */
    public function testGetControllerName(): void
    {
        $postData = [
            'tx_femanager_pi1' => [
                'controller' => 'foo'
            ]
        ];

        $request = (new ServerRequest('POST', '/'))
            ->withParsedBody($postData);

        self::assertSame('foo', FrontendUtility::getControllerName($request));
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getActionName
     */
    public function testGetActionName(): void
    {
        $postData = [
            'tx_femanager_pi1' => [
                'action' => 'bar'
            ]
        ];

        $request = (new ServerRequest('POST', '/'))
            ->withParsedBody($postData);

        self::assertSame('bar', FrontendUtility::getActionName($request));
    }
}

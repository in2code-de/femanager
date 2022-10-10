<?php

namespace In2code\Femanager\ViewHelpers\Request;

use In2code\Femanager\ViewHelpers\Misc\RequestViewHelper;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class RequestTest
 * @coversDefaultClass In2code\Femanager\ViewHelpers\Misc\RequestViewHelper
 */
class RequestTest extends UnitTestCase
{
    /**
     * @var \TYPO3\CMS\Core\Tests\AccessibleObjectInterface
     */
    protected $abstractValidationViewHelperMock;

    public function setUp(): void
    {
        $this->abstractValidationViewHelperMock = $this->getAccessibleMock(RequestViewHelper::class, ['dummy']);
    }

    public function tearDown(): void
    {
        unset($this->abstractValidationViewHelperMock);
    }

    /**
     * @return array
     */
    public function renderReturnsStringDataProvider()
    {
        return [
            [
                'L',
                true,
                [
                    'L' => '123'
                ],
                '123'
            ],
            [
                'test',
                true,
                [
                    'test' => '>'
                ],
                '&gt;'
            ],
            [
                'tx_test|sword',
                true,
                [
                    'tx_test' => [
                        'sword' => 'abc'
                    ]
                ],
                'abc'
            ],
            [
                'tx_test_pi1|abc|def',
                true,
                [
                    'tx_test_pi1' => [
                        'abc' => [
                            'def' => 'xyz'
                        ]
                    ]
                ],
                'xyz'
            ],
            [
                'asfd|abc|def|ghi',
                true,
                [
                    'asfd' => [
                        'abc' => [
                            'def' => [
                                'ghi' => '7x'
                            ]
                        ]
                    ]
                ],
                '7x'
            ],
            [
                'abc',
                true,
                [],
                ''
            ],
        ];
    }

    /**
     * @param string $parameter
     * @param bool $htmlSpecialChars
     * @param array $parametersToSet
     * @param string $expectedResult
     * @dataProvider renderReturnsStringDataProvider
     * @covers ::render
     */
    public function testRenderReturnsString($parameter, $htmlSpecialChars, $parametersToSet, $expectedResult)
    {
        $arguments = [
            'parameter' => $parameter,
            'htmlspecialchars' => $htmlSpecialChars,
            'parametersToSet' => $parametersToSet
        ];

        $this->abstractValidationViewHelperMock->_set('arguments', $arguments);
        $this->abstractValidationViewHelperMock->_set('testVariables', $parametersToSet);

        $result = $this->abstractValidationViewHelperMock->_call('render', $parameter, $htmlSpecialChars);
        self::assertSame($expectedResult, $result);
    }
}

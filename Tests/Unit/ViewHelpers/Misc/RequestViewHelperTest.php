<?php
namespace In2code\Femanager\ViewHelpers\Request;

use In2code\Femanager\ViewHelpers\Misc\RequestViewHelper;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class RequestTest
 * @coversDefaultClass In2code\Femanager\ViewHelpers\Misc\RequestViewHelper
 */
class RequestTest extends UnitTestCase
{

    /**
     * @var RequestViewHelper
     */
    protected $generalValidatorMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->generalValidatorMock = $this->getAccessibleMock(RequestViewHelper::class, ['dummy']);
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        unset($this->generalValidatorMock);
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
                null
            ],
        ];
    }

    /**
     * @param string $parameter
     * @param bool $htmlSpecialChars
     * @param array $parametersToSet
     * @param string $expectedResult
     * @dataProvider renderReturnsStringDataProvider
     * @return void
     * @covers ::render
     */
    public function testRenderReturnsString($parameter, $htmlSpecialChars, $parametersToSet, $expectedResult)
    {
        $this->generalValidatorMock->_set('testVariables', $parametersToSet);
        $result = $this->generalValidatorMock->_call('render', $parameter, $htmlSpecialChars);
        $this->assertSame($expectedResult, $result);
    }
}

<?php

namespace In2code\Femanager\Tests\Unit\ViewHelpers\Misc;

use In2code\Femanager\ViewHelpers\Misc\RequestViewHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class RequestTest
 * @coversDefaultClass \In2code\Femanager\ViewHelpers\Misc\RequestViewHelper
 */
class RequestViewHelperTest extends UnitTestCase
{
    protected AccessibleObjectInterface|MockObject|RequestViewHelper $abstractValidationViewHelperMock;

    public function setUp(): void
    {
        $this->abstractValidationViewHelperMock = $this->getAccessibleMock(
            RequestViewHelper::class,
            null
        );
    }

    public function tearDown(): void
    {
        unset($this->abstractValidationViewHelperMock);
    }

    public static function renderReturnsStringDataProvider(): array
    {
        return [
            [
                'L',
                true,
                [
                    'L' => '123',
                ],
                '123',
            ],
            [
                'test',
                true,
                [
                    'test' => '>',
                ],
                '&gt;',
            ],
            [
                'tx_test|sword',
                true,
                [
                    'tx_test' => [
                        'sword' => 'abc',
                    ],
                ],
                'abc',
            ],
            [
                'tx_test_pi1|abc|def',
                true,
                [
                    'tx_test_pi1' => [
                        'abc' => [
                            'def' => 'xyz',
                        ],
                    ],
                ],
                'xyz',
            ],
            [
                'asfd|abc|def|ghi',
                true,
                [
                    'asfd' => [
                        'abc' => [
                            'def' => [
                                'ghi' => '7x',
                            ],
                        ],
                    ],
                ],
                '7x',
            ],
            [
                'abc',
                true,
                [],
                '',
            ],
        ];
    }

    /**
     * @dataProvider renderReturnsStringDataProvider
     * @covers ::render
     */
    public function testRenderReturnsString(
        string $parameter,
        bool $htmlSpecialChars,
        array $parametersToSet,
        string $expectedResult
    ): void {
        $arguments = [
            'parameter' => $parameter,
            'htmlspecialchars' => $htmlSpecialChars,
            'parametersToSet' => $parametersToSet,
        ];

        $this->abstractValidationViewHelperMock->_set('arguments', $arguments);
        $this->abstractValidationViewHelperMock->_set('testVariables', $parametersToSet);

        $request = $this->createMock(ServerRequestInterface::class);

        $request->method('getParsedBody')->willReturn($parametersToSet);
        $request->method('getQueryParams')->willReturn($parametersToSet);

        $renderingContext = $this->createMock(RenderingContextInterface::class);
        $renderingContext->method('getAttribute')
            ->with(ServerRequestInterface::class)
            ->willReturn($request);

        $this->abstractValidationViewHelperMock->setRenderingContext($renderingContext);

        $result = $this->abstractValidationViewHelperMock->_call('render');
        self::assertSame($expectedResult, $result);
    }
}

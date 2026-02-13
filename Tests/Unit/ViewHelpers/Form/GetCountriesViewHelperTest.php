<?php

namespace In2code\Femanager\Tests\Unit\ViewHelpers\Form;

use In2code\Femanager\ViewHelpers\Form\GetCountriesViewHelper;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class GetCountriesTest
 * @coversDefaultClass \In2code\Femanager\ViewHelpers\Form\GetCountriesViewHelper
 */
class GetCountriesViewHelperTest extends UnitTestCase
{
    protected GetCountriesViewHelper $viewHelperMock;
    protected ServerRequestInterface $requestMock;

    public function setUp(): void
    {
        parent::setUp();

        $listenerProviderMock = $this->getMockBuilder(ListenerProviderInterface::class)->getMock();
        $eventDispatcher = new EventDispatcher($listenerProviderMock);

        $this->viewHelperMock = $this->getAccessibleMock(
            GetCountriesViewHelper::class,
            null,
            [
                new CountryProvider($eventDispatcher),
                $this->getMockBuilder(LanguageServiceFactory::class)->disableOriginalConstructor()->getMock(),
            ]
        );

        $this->requestMock = $this->getMockBuilder(ServerRequestInterface::class)->getMock();

        $siteLanguageMock = $this->getMockBuilder(SiteLanguage::class)->disableOriginalConstructor()->getMock();
        $this->requestMock->method('getAttribute')->with('language')->willReturn($siteLanguageMock);
        $contextMock = $this->getMockBuilder(\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface::class)->getMock();
        $contextMock->method('hasAttribute')->with(ServerRequestInterface::class)->willReturn(true);
        $contextMock->method('getAttribute')->with(ServerRequestInterface::class)->willReturn($this->requestMock);

        $this->viewHelperMock->setRenderingContext($contextMock);
    }

    public function tearDown(): void
    {
        unset($this->viewHelperMock);
    }

    /**
     * @covers ::render
     */
    public function testRenderReturnArray(): void
    {
        $result = $this->viewHelperMock->_call('render');
        self::assertArrayHasKey('DEU', $result);
        self::assertArrayHasKey('FRA', $result);
        self::assertArrayHasKey('SWZ', $result);
    }
}

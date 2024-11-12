<?php

namespace In2code\Femanager\Tests\Unit\ViewHelpers\Form;

use In2code\Femanager\ViewHelpers\Form\GetCountriesViewHelper;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class GetCountriesTest
 * @coversDefaultClass \In2code\Femanager\ViewHelpers\Form\GetCountriesViewHelper
 */
class GetCountriesViewHelperTest extends UnitTestCase
{
    protected GetCountriesViewHelper $generalValidatorMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->generalValidatorMock = $this->getAccessibleMock(
            GetCountriesViewHelper::class,
            null,
            [
                new CountryProvider(),
                $this->getMockBuilder(LanguageServiceFactory::class)->disableOriginalConstructor()->getMock(),
            ]
        );
    }

    public function tearDown(): void
    {
        unset($this->generalValidatorMock);
    }

    /**
     * @covers ::render
     */
    public function testRenderReturnArray(): void
    {
        $request = (new ServerRequest())->withAttribute('language', new SiteLanguage(
            0,
            'en',
            new Uri('/'),
            []
        ));
        $GLOBALS['TYPO3_REQUEST'] = $request;
        $result = $this->generalValidatorMock->_call('render');
        self::assertArrayHasKey('DEU', $result);
        self::assertArrayHasKey('FRA', $result);
        self::assertArrayHasKey('SWZ', $result);
    }
}

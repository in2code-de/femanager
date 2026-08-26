<?php

declare(strict_types=1);

namespace In2code\Femanager\Tests\Unit\Middleware;

use In2code\Femanager\Middleware\CleanUserGroupMiddleware;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(CleanUserGroupMiddleware::class)]
class CleanUserGroupMiddlewareTest extends UnitTestCase
{
    protected CleanUserGroupMiddleware $subject;

    private ?ServerRequestInterface $handledRequest = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new CleanUserGroupMiddleware();
    }

    /**
     * The usergroup select renders an empty "please choose" option. If it is submitted unchanged,
     * the empty index has to be dropped so the property mapper does not try to map it.
     */
    #[Test]
    public function emptyUsergroupSelectionIsRemovedFromRegistrationBody(): void
    {
        $result = $this->process($this->registrationBody(['usergroup' => ['']]));

        self::assertSame($this->registrationBody(['usergroup' => []]), $result);
    }

    #[Test]
    public function submittedUsergroupUidIsKept(): void
    {
        $body = $this->registrationBody(['usergroup' => ['2']]);

        self::assertSame($body, $this->process($body));
    }

    #[Test]
    public function existingRelationIsKeptWhenIdentityIsSubmitted(): void
    {
        $body = $this->registrationBody(['usergroup' => ['', '__identity' => '5']]);

        self::assertSame($body, $this->process($body));
    }

    #[Test]
    public function nonArrayUsergroupIsUntouched(): void
    {
        $body = $this->registrationBody(['usergroup' => '2']);

        self::assertSame($body, $this->process($body));
    }

    #[Test]
    public function bodyWithoutFemanagerKeysIsPassedThrough(): void
    {
        $body = ['some_other_plugin' => ['field' => 'value']];

        self::assertSame($body, $this->process($body));
    }

    #[Test]
    public function nullParsedBodyDoesNotFail(): void
    {
        self::assertNull($this->process(null));
    }

    #[Test]
    public function originalRequestIsNotModified(): void
    {
        $body = $this->registrationBody(['usergroup' => ['']]);
        $request = (new ServerRequest())->withParsedBody($body);

        $this->subject->process($request, $this->handler());

        self::assertSame($body, $request->getParsedBody());
        self::assertSame($this->registrationBody(['usergroup' => []]), $this->handledRequest->getParsedBody());
    }

    /**
     * Pins a known limitation: only the registration plugin namespace is cleaned, the edit and
     * invitation forms post to their own namespaces and are left as they are.
     */
    #[Test]
    public function editAndInvitationNamespacesAreNotCleaned(): void
    {
        $body = [
            'tx_femanager_edit' => ['user' => ['usergroup' => ['']]],
            'tx_femanager_invitation' => ['user' => ['usergroup' => ['']]],
        ];

        self::assertSame($body, $this->process($body));
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private function registrationBody(array $user): array
    {
        return [
            'tx_femanager_registration' => [
                'user' => array_merge(['username' => 'jane'], $user),
            ],
        ];
    }

    private function process(mixed $parsedBody): mixed
    {
        $request = (new ServerRequest())->withParsedBody($parsedBody);

        $this->subject->process($request, $this->handler());

        return $this->handledRequest->getParsedBody();
    }

    private function handler(): RequestHandlerInterface
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())->method('handle')->willReturnCallback(
            function (ServerRequestInterface $request): ResponseInterface {
                $this->handledRequest = $request;

                return new Response();
            }
        );

        return $handler;
    }
}

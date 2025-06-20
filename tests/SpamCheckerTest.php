<?php

namespace App\Tests;

use App\Entity\Comment;
use App\SpamChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SpamCheckerTest extends TestCase
{
    /**
     * @throws TransportExceptionInterface
     */
    public function testSpamScoreWithInvalidRequest(): void
    {
        $comment = new Comment();
        $comment->setAuthor('MALEK');
        $comment->setEmail('MALEK@gmail.com');
        $comment->setCreatedAtValue();
        $context = [];
        $client = new MockHttpClient([
            new MockResponse(
            'invalid',
            [
                'response_headers' => ['x-askismet-debug-help: Invalid key']
            ]
        )]);
        $checker = new SpamChecker($client, '88a76d534a2d');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(trim('Unable to check for spam : invalid (Invalid key)'));
        $checker->getSpamScore($comment, $context);
    }

    /**
     * @dataProvider provideComments
     * @throws TransportExceptionInterface
     */
    public function testSpamScore(int $expectedScore, ResponseInterface $response, Comment $comment, array $context)
    {
        $client = new MockHttpClient([$response]);
        $checker = new SpamChecker($client, 'abcde');
        $score = $checker->getSpamScore($comment, $context);
        $this->assertSame($expectedScore, $score);
    }

    public static function provideComments(): iterable
    {
        $comment = new Comment();
        $comment->setCreatedAtValue();
        $context = [];
        $response = new MockResponse(
            '',
            [
                'response_headers' => ['x-askismet-pro-tip: discard']
            ]
        );
        yield 'blatant_spam' => [2, $response, $comment, $context];
        $response = new MockResponse('true');
        yield 'spam' => [1, $response, $comment, $context];
        $response = new MockResponse('false');
        yield 'ham' => [0, $response, $comment, $context];
    }
}

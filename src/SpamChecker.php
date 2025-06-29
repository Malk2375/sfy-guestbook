<?php

namespace App;

use App\Entity\Comment;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpamChecker
{
    private string $endpoint;
    public function __construct(
        private readonly HttpClientInterface     $client,
//        string $askimetKey,
        #[Autowire('%env(ASKIMET_KEY)%')] string $askimetKey
    ){
        $this->endpoint = sprintf('https://%s.rest.askimet.com/1.1/comment-check', $askimetKey);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function getSpamScore(Comment $comment, array $context): int
    {
        $response = $this->client->request(
            'POST',
            $this->endpoint,
            [
                'body' => array_merge($context, [
                    'https://127.0.0.1:8000',
                    'comment_type' => 'comment',
                    'comment_author' => $comment->getAuthor(),
                    'comment_author_email' => $comment->getEmail(),
                    'comment_content' => $comment->getText(),
                    'comment_date_gmt' => $comment->getCreatedAt()->format('c'),
                    'blog_lang' => 'en',
                    'blog_charset' => 'UTF-8',
                    'is_test' => true,
                ]),
            ]
        );
        $headers = $response->getHeaders();
        if ('discard' === ($headers['x-askismet-pro-tip'][0] ?? '')){
            return 2;
        }
        $content = $response->getContent();
        if (isset($headers['x-askismet-debug-help'][0])){
            throw new \RuntimeException(sprintf('Unable to check for spam : %s (%s)', $content, $headers['x-askismet-debug-help'][0]));
        }

        return 'true' === $content ? 1 : 0;
    }
}
<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback');
    }

    public function testConferencePage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/conference/toulouse-2020');

        // Vérifie combien d'éléments <h4> sont présents
        dump($crawler->filter('h5')->count());

        // Vérifie que tu as bien 3 éléments <h4>
        $this->assertCount(1, $crawler->filter('h5'));
        $client->clickLink('View');

        $this->assertPageTitleContains('Toulouse');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h5', 'Toulouse 2020 Conference');
        $titles = $crawler->filter('title')->each(function ($node) {
            return $node->text();
        });
        dump($titles);
        $comments = $crawler->filter('div')->each(function ($node) {
            return $node->text();
        });
        dump($comments);

        $this->assertSelectorExists('div:contains("There are 2 comments.")');
    }

    public function testCommentSubmission(): void
    {
        $client = static::createClient();
        $client->request('GET', '/conference/toulouse-2020');
        dump($client->getResponse()->getContent());
        $client->submitForm('Submit', [
            'comment_form[author]' => 'Malek Testeur',
            'comment_form[email]' => 'me@automat.ed',
            'comment_form[text]' => 'Test comment',
            'comment_form[photo]' => dirname(__DIR__, 2).'/public/images/under-construction.png',
        ]);
        $this->assertResponseRedirects();

        $client->followRedirect();
        // Après la redirection
        dump($client->getResponse()->getContent());

//        $this->assertResponseRedirects();
        $this->assertSelectorExists('div:contains("There are 3 comments.")');
    }
}
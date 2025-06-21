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
        $crawler = $client->request('GET', '/');

        // Vérifie combien d'éléments <h4> sont présents
        dump($crawler->filter('h5')->count());

        // Vérifie que tu as bien 3 éléments <h4>
        $this->assertCount(2, $crawler->filter('h5'));
        $client->clickLink('View');

        $this->assertPageTitleContains('Toulouse');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h5', 'Toulouse 2020 Conference');
        $titles = $crawler->filter('title')->each(function ($node) {
            return $node->text();
        });
        dump($titles);
        $this->assertSelectorExists('div:contains("No comments")');
    }
//
//    public function testConferencePage(): void
//    {
//        $client = static::createClient();
//        $crawler = $client->request('GET', '/');
//        $this->assertCount(2, $crawler->filter('h5'));
////        $client->clickLink('View');
////        $this->assertPageTitleContains('Toulouse');
////        $this->assertResponseIsSuccessful();
////        $this->assertSelectorExists('h5');
////        $this->assertSelectorTextContains('h5', 'Toulouse 2020');
////        $this->assertSelectorExists('div:contains("There are 1 comments")');
//    }
}
<?php

namespace App\Service;

class ArticleService
{
    // ArticleService.php ou équivalent
    public function loadArticles(): \Generator
    {
//        echo str_repeat(' ', 2);
        for ($i = 1; $i <= 20; $i++) {
            ob_flush();
            flush();
            yield [
                'id' => $i,
                'title' => "Article $i",
                'created_at' => (new \DateTime())->format('H:i:s'),
            ];

            sleep(1); // pause de 1 seconde pour simuler le streaming
            flush();
        }
    }

}
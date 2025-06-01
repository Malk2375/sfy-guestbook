<?php

namespace App\Controller;

use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;

class ConferenceController extends AbstractController
{
//    private ArticleService $articleService;
//    public function __construct(ArticleService $articleService)
//    {
//        $this->articleService = $articleService;
//    }
    #[Route('/', name: 'homepage')]
    public function index(Request $request): Response
    {
//        $request->getSession()->set('attribute-name', 'attribute-value');
        dump($request);
        $greet ='';
        if ($name = $request->query->get('hello')) {
            $greet = sprintf('<h1>Hello %s</h1>', htmlspecialchars($name));
        }
        return new Response(<<<EOF
<html lang="fr">
    <body>
        $greet
        <img src="images/under-construction.png"  alt=""/>
    </body>
</html>
EOF
);
    }
//
//    #[Route('/test-streamed-response', name: 'homepage')]
//    public function indexStream(): StreamedResponse
//    {
//        $response = new StreamedResponse(function () {
//            // Désactiver tous les buffers PHP
//            while (ob_get_level() > 0) {
//                ob_end_flush();
//            }
//
//            // Forcer un premier gros chunk (1 à 2 Ko) pour débloquer les navigateurs
//            echo str_repeat(' ', 2048);
//            echo "Début du flux<br>";
//            flush();
//
//            for ($i = 1; $i <= 5; $i++) {
//                echo "Ligne $i - " . (new \DateTime())->format('H:i:s') . "<br>";
//                flush();
//                sleep(5);
//            }
//        });
//
//        $response->headers->set('Content-Type', 'text/html');
//        return $response;
//    }
//
//    #[Route('/stream-articles', name: 'stream_articles')]
//    public function streamArticles(): Response
//    {
//        return new StreamedJsonResponse([
//            '_embedded' => [
//                'articles' => $this->articleService->loadArticles(),
//            ],
//        ]);
//    }

}

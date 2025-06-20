<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ConferenceController extends AbstractController
{
//    private ArticleService $articleService;
//    public function __construct(ArticleService $articleService)
//    {
//        $this->articleService = $articleService;
//    }

//    #[Route('/', name: 'homepage')]
//    public function index(Request $request, ConferenceRepository $conferenceRepository): Response
//    {
////        $request->getSession()->set('attribute-name', 'attribute-value');
//        dump($request);
//        $conference =$conferenceRepository->find(1);
////        if ($name = $request->query->get('hello')) {
////            $greet = sprintf('<h1>Hello %s</h1>', htmlspecialchars($name));
////        }
//        return new Response(<<<EOF
//<html lang="fr">
//    <body>
//        $conference
//        <img src="images/under-construction.png"  alt=""/>
//    </body>
//</html>
//EOF
//);
//    }


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


    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param ConferenceRepository $conferenceRepository
     * @return Response
     */
    #[Route('/', name: 'homepage')]
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        $conferences = $conferenceRepository->findAll();
        return new Response($this->render('conference/index.html.twig', [
            'conferences' => $conferences,
        ]));
    }


    /**
     * @param Request $request
     * @param Conference $conference
     * @param CommentRepository $commentRepository
     * @param string $photoDir
     * @param SpamChecker $spamChecker
     * @return Response
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    #[Route('/conference/{slug}', name: 'conference')]
    public function show(
        Request $request,
        Conference $conference,
        CommentRepository $commentRepository,
        #[Autowire('%photo_dir%')] string $photoDir,
        SpamChecker $spamChecker,
    ): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);
            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $e) {
                    // GIVE UP , NOT ABLE TO UPLOAD
                }
                $comment->setPhotoFilename($filename);
            }
            $this->entityManager->persist($comment);
            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('use-agent'),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri()
            ];
            if (2 === $spamChecker->getSpamScore($comment, $context)){
                throw new \RuntimeException('We do not accept SPAM here !');
            }
            $this->entityManager->flush();
            return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
        }
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);
        return new Response($this->render('conference/show.html.twig', [
//             'conferences' => $conferenceRepository->findAll(),
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'comment_form' => $form,
        ]));
    }
}

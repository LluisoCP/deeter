<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\DeetRepository;
use App\Form\DeetType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Deet;
use App\Repository\HashtagRepository;
use App\Entity\Hashtag;

class PagesController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, DeetRepository $deet_repository, HashtagRepository $hashtag_repository)
    {
        //preg_match_all("/(#\w+)/", $tweet, $hashtags, PREG_SET_ORDER);
        /**
         * 
        * php > $results = [];
        * php > foreach ( $founds as $found ) { $results[] = $found[0]; }
        * php > var_dump($results);
         */
        $deet = new Deet();
        $form = $this->createForm(DeetType::class, $deet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $content = $deet->getContent();
            preg_match_all("/(#\w+)/", $content, $results, PREG_SET_ORDER);
            $hashtags = [];
            foreach ($results as $result) {
                $hashtags[] = $result[0];
            }
            // dd($hashtags);
            $em = $this->getDoctrine()->getManager();
            foreach ($hashtags as $h) {

                $hashtag = $hashtag_repository->findOneBy(['title' => $h]);

                if (!$hashtag) {
                    $hashtag = new Hashtag();
                    $hashtag->setTitle($h);

                    $em->persist($hashtag);
                    $em->flush();
                }

                $deet->addHashtag($hashtag);
                $em->persist($deet);
                $em->flush();
                
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($deet);
            $em->flush();

            // Display an empty form after submission
            unset($form);
            unset($deet);
            $deet = new Deet();
            $form = $this->createForm(DeetType::class, $deet);
        }




        $deets = $deet_repository->findAll();
        return $this->render('pages/index.html.twig', [
            'deets' => $deets,
            'form'  => $form->createView(),
        ]);
    }
}

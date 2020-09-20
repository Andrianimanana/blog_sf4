<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request; 
use App\Entity\User;
use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType; 
use App\Constant\MessageConstant;

class BlogController extends AbstractController
{
     
    public function index()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(
            ["isPublised"        => true],
            ["publicationDate"   => "DESC"]
        );
        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    public function show(Article $article, Request $request)
    {
		# comment
        $new_com    = new Comment();
        $form_com   = $this->createForm(CommentType::class, $new_com);
        $form_com->handleRequest($request);
        # list of comment on article
        $list_com   = $this->getDoctrine()->getRepository(Comment::class)->findBy(
            ["article" => $article->getId()]
        ); 

        return $this->render('blog/show.html.twig', [
			'article'  => $article,
            "comments" => $list_com,
            'form_com' => $form_com->createView()
		]);		
    }

    public function comment(Article $article, Comment $comment = null, Request $request){

        if(!$this->getUser() && $article){
            $this->addFlash(MessageConstant::ERROR_TYPE, MessageConstant::ERROR_AUTH_USER_MESSAGE);
            return $this->redirectToRoute('app_login');
        }

        $em     = $this->getDoctrine()->getManager();
        $com    = $comment ?? new Comment();
        $form   = $this->createForm(CommentType::class, $com);
        $form->handleRequest($request);
                
        if($form->isSubmitted() && $form->isValid()){            
            $com->setDateComment(new \DateTime());
            $com->setArticle($article);
            $com->setUser($this->getUser());
            $em->persist($com);
            $em->flush(); 
        }

        return $this->redirectToRoute('show_article', ['id' => $article->getId()]);
    }
}

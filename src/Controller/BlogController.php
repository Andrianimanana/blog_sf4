<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ArticleType;
use App\Entity\Article;
use App\Entity\User;
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

    public function add(Request $request)
    {
        $pass       = false;
        $article    = new Article();
        $form       = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $date    = new \DateTime();
            $article->setLastUpdateDate($date);
            $article->setPublicationDate(new \DateTime('0000-00-00 00:00:00'));
            # publié
            if($article->getIsPublised())
                $article->setPublicationDate($date);
           
            $picture = $form->get('picture')->getData();
            # upload file
            if($picture){
                $orgin_file_name = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
                $new_file_name   = $orgin_file_name.'_'.uniqid().'.'.$picture->guessExtension();
                
                try {
                    $picture->move($this->getParameter('upload_directory'), $new_file_name);
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $article->setPicture($new_file_name);                    
            }    

            $em     = $this->getDoctrine()->getManager();
            try {
                $em->persist($article);
                $em->flush();
                $pass = true;
            } catch (Exception $e) {
                $this->addFlash(MessageConstant::ERROR_TYPE, $e);                
            }
        }

        if($pass){
            $this->addFlash(MessageConstant::SUCCESS_TYPE, MessageConstant::AJOUT_MESSAGE);
            
            return $this->redirectToRoute('admin');
        }

        return $this->render('blog/add.html.twig', [
            'form'      => $form->createView(),
            'article'   => $article
        ]);		    	
    }

    public function show(Article $article)
    {
		return $this->render('blog/show.html.twig', [
			'article' => $article
		]);		
    }

    public function edit(Article $article, Request $request)
    {
        $old_picture    = $article->getPicture();
        $form           = $this->createForm(ArticleType::class, $article);
        # dd($form);
        $form->handleRequest($request);

        #
        if( $form->isSubmitted() && $form->isValid() ){
            if($article->getIsPublised())
                $article->setPublicationDate(new \DateTime());
            $article->setLastUpdateDate(new \DateTime());
            # edit picture
            if($article->getPicture() !== $old_picture && $article->getPicture() !== null){
                $picture            = $form->get('picture')->getData();
                $orgin_file_name    = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
                $new_file_name      = $orgin_file_name.'_'.uniqid().'.'.$picture->guessExtension();
                #
                try {
                    $picture->move($this->getParameter('upload_directory'), $new_file_name);     
                } catch (FileException $e) {
                     return new Response($e->getMessage());
                } 

                $article->setPicture($new_file_name);                
                # delete old picture
                @unlink($this->getParameter('upload_directory').'/'.$old_picture);
            }else{
                $article->setPicture($old_picture);
            }

            $em = $this->getDoctrine()->getManager(); 
            $em->flush();

            return $this->redirectToRoute('admin');
        }
        
        return $this->render('blog/edit.html.twig', [
            'article'   => $article,
            'form'      => $form->createView()
		]);		
    }

    public function remove(Article $article)
    {
		
        $em     = $this->getDoctrine()->getManager();
        $pass   = false;
        try {
            $em->remove($article);
            $em->flush();
            $pass = true;        
        } catch (Exception $error) {
            # @toDo
            $this->addFlash(MessageConstant::ERROR_TYPE, $error);
        } 
        if($pass)
            $this->addFlash(MessageConstant::SUCCESS_TYPE, MessageConstant::SUPPRESSION_MESSAGE);
        return $this->redirectToRoute('admin');
    }

    public function admin(){
        # list of article
        $articles   = $this->getDoctrine()->getRepository(Article::class)->findAll();
        # list of user
        $users      = $this->getDoctrine()->getRepository(User::class)->findAll();
        return $this->render('admin/index.html.twig', [
            "articles"  => $articles,
            "users"     => $users
        ]);
    }
}

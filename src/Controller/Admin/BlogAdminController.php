<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Exception;
use App\Constant\MessageConstant;
use App\Form\ArticleType;
use App\Entity\Article;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Constant\NumberConstant;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/admin_old")
 */
class BlogAdminController extends AbstractController
{
	private $em;
	public function __construct(EntityManagerInterface $em){
		$this->em = $em;
	}

	/**
	 * @Route("/",name="admin") 
	 */
	public function index(PaginatorInterface $paginator, Request $request){
        # list of article
		$articles   = $this->getDoctrine()->getRepository(Article::class)->findAll();
		
        $articles_pagined = $paginator->paginate(
            $articles, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            NumberConstant::NB_PER_PAGE // Nombre de résultats par page
		);		
        # list of user
		$users      = $this->getDoctrine()->getRepository(User::class)->findAll();
		return $this->render('admin/index.html.twig', [
			"articles"  => $articles_pagined,
			"users"     => $users
		]);
	}

	/**
	 * @Route("/add",name="add_article",methods={"POST","GET"})
	 */
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
            
            # upload file
			if($form->get('picture')->getData()){ 
				$new_file_name = $this->uploadImage($form);
				if($new_file_name["upload"])
					$article->setPicture($new_file_name["new_file_name"]);     
			}    

			try {
				$this->em->persist($article);
				$this->em->flush();
				$pass = true;
			} catch (Exception $e) {
				$this->addFlash(MessageConstant::ERROR_TYPE, $e);                
			}
		}

		if($pass){
			$this->addFlash(MessageConstant::SUCCESS_TYPE, MessageConstant::AJOUT_MESSAGE);

			return $this->redirectToRoute('admin');
		}

		return $this->render('admin/add.html.twig', [
			'form'      => $form->createView(),
			'article'   => $article
		]);		    	
	}

	/**
	 * @Route("/edit/{id}/article",name="edit_article",methods={"POST","GET"})
	 */
	public function edit(Article $article, Request $request)
	{
		$old_picture    = $article->getPicture();		
		$form           = $this->createForm(ArticleType::class, $article);
		$form->handleRequest($request);
        #
		if( $form->isSubmitted() && $form->isValid() ){
			if($article->getIsPublised())
				$article->setPublicationDate(new \DateTime());
			$article->setLastUpdateDate(new \DateTime());
            # edit picture			
			if( !is_null($form->get('picture')->getData()) ){
				$new_file_name = $this->uploadImage($form, 'edit');			
				
				if($new_file_name["upload"]){
					$article->setPicture($new_file_name["new_file_name"]);
					@unlink($this->getParameter('app.dir.upload').'/'.$old_picture);
				}
			}else{ 
				$article->setPicture($old_picture);
			}
				 
			$this->em->flush();
			return $this->redirectToRoute('admin');
		}

		return $this->render('admin/edit.html.twig', [
			'article'   => $article,
			'form'      => $form->createView()
		]);		
	}

	/**
	 * @Route("/remove/{id}/article",name="remove_article")
	 */
	public function remove(Article $article)
	{

		$pass   = false;
		try {
			$this->em->remove($article);
			$this->em->flush();
			$pass = true;        
		} catch (Exception $error) {
                # @toDo
			$this->addFlash(MessageConstant::ERROR_TYPE, $error);
		} 
		if($pass)
			$this->addFlash(MessageConstant::SUCCESS_TYPE, MessageConstant::SUPPRESSION_MESSAGE);
		return $this->redirectToRoute('admin');
	}

	private function uploadImage($form, $action= 'add'){
		$picture            = $form->get('picture')->getData();
		$orgin_file_name    = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
		$new_file_name      = $orgin_file_name.'_'.uniqid().'.'.$picture->guessExtension();
	    
		try {
			$picture->move($this->getParameter('app.dir.upload'), $new_file_name);     
			return ["new_file_name"=> $new_file_name, "upload" => true];
		} catch (FileException $e) {
			return new Response($e->getMessage());
		}                 

	}

}

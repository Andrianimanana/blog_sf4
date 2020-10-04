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
use App\Entity\Reply;
use App\Form\CommentType; 
use App\Form\ReplyType;
use App\Constant\MessageConstant;

class BlogController extends AbstractController
{
    public const BLOG_CONST = [
        'display_reply' => 'display_reply',
        'display_form'  => 'display_form',
        'show_comment'  => 'show_comment'
    ];
     
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

    public function show(Article $article)
    {
		# comment
        $new_com    = new Comment();
        $form_com   = $this->createForm(CommentType::class, $new_com);
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

    public function replyComment(Comment $comment, Request $request){
        $reply      = new Reply();
        $form_reply = $this->createForm(ReplyType::class, $reply) ;
        $form_reply->handleRequest($request);
        $action     = self::BLOG_CONST["display_form"];
        
        if(!$this->getUser())
            exit('0');

        if(null !== $request->request->get('reply') && array_key_exists("reply", $request->request->get('reply'))){
            $em = $this->getDoctrine()->getManager();
            $data = $request->request->get('reply');
            $reply->setReply($data['reply']);
            $reply->setUser($this->getUser());
            $reply->setComment($comment);
            $reply->setLastupdate(new \DateTime());
            $em->persist($reply);
            $em->flush(); 
            $action = self::BLOG_CONST["show_comment"]; 
        }
            
        return $this->render('blog/reply.html.twig', [
            'reply'         => $reply,
            'comment'       => $comment,
            'action'        => $action,
            'form_reply'    => $form_reply->createView()
        ]);
    }

    public function displayReply(Comment $comment, Request $request){
        
        if(null !== $request->request->get('display_only_form')){
            $reply      = new Reply();
            $form_reply = $this->createForm(ReplyType::class, $reply);
            
            return $this->render('blog/reply.html.twig', [
                'comment'   => $comment,
                'action'    => self::BLOG_CONST["display_form"],
                'form_reply'=> $form_reply->createView()
            ]);   
        }else{
            $replies    = $comment->getReplies();        
            
            if(!$replies->count())
                exit("0");  
            
            return $this->render('blog/reply.html.twig', [
                'replies'   => $replies,
                'comment'   => $comment,
                'action'    => self::BLOG_CONST["display_reply"]
            ]);      
        }    
    }
}

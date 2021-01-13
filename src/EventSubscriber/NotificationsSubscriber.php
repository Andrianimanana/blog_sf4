<?php

/**
 * @Author: Armel <arbandry@gmail.com>
 */
namespace App\EventSubscriber;

use App\Event\CommentBlogEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationsSubscriber implements EventSubscriberInterface
{
    private $mailer;
    
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer   = $mailer;
    }

    public function onAppCommentCreated(CommentBlogEvent $event)
    {
        $user       = $event->user;
        $comment    = $event->comment;
        $idarticle  = $comment->getArticle()->getId();
        //
        $subject    = "COMMENTAIRE ARTICLE ID: <strong>$idarticle</strong>";
        $body       = "<cenetr><h1>". $user->getUsername() . " vient de commenter l'article ID: ".$idarticle. "</h1><br><br>";
        $body       .= "Commentaire : <strong>".$comment->getComment()."</strong></cenetr>";
        // mail
        $email = (new Email())
            ->from($comment->getUser()->getEmail())
            ->to('arbandry@gmail.com')//@toDo: récuperer dupuis config file
            ->subject($subject)
            ->html($body)
        ;
        // send mail
        $this->mailer->send($email);
    }

    public static function getSubscribedEvents()
    {
        return [
            CommentBlogEvent::NAME => 'onAppCommentCreated',
        ];
    }
}

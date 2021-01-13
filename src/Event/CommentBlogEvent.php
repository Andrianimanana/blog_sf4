<?php

/**
 * @Author: Armel <arbandry@gmail.com>
 */
namespace App\Event;

use App\Entity\Comment;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;


class CommentBlogEvent extends Event
{
	protected $comment;
	protected $user;
	public const NAME = 'app.comment.created';

	public function __construct(User $user, Comment $comment)
	{
		$this->user 	= $user;
		$this->comment 	= $comment;
	}

	public function getUser(): User 
	{
		return $this->user;
	}

	public function getComment(): Comment 
	{
		return $this->comment;
	}

	public function __get($name= '')
	{
		switch ($name) {
			case 'user'		: return $this->user; break;
			case 'comment' 	: return $this->user; break;			
			default 		: dd("Parameter $name not valid"); break;
		}
	}
}
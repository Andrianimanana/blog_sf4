<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
     
    public function index()
    {
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
        ]);
    }

    public function add()
    {
		return $this->render('blog/add.html.twig');		    	
    }

    public function show($url)
    {
		return $this->render('blog/show.html.twig', [
			'slug' => $url
		]);		
    }

    public function edit($id)
    {
		return $this->render('blog/edit.html.twig', [
			'id' => $id
		]);		
    }

    public function remove($id)
    {
		return $this->render('blog/index.html.twig');		
    }
}

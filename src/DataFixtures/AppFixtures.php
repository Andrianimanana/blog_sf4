<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Article;
use App\Entity\Comment;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker;

class AppFixtures extends Fixture
{
   private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        // On configure dans quelles langues nous voulons nos données
        $faker = Faker\Factory::create('fr_FR');
        $categories = [];
        for($i=1;$i<5;$i++){
        	# category 
        	$category 	=  new Category();
        	$category->setLibele("Categorie ".$i);
        	$category->setLastupdate($faker->dateTime);
        	array_push($categories, $category);
        	# user
        	$roles = ["ROLE_USER", "ROLE_ADMIN"];
        	$user 	  = new User();
        	$password = $this->encoder->encodePassword($user, 'elle');
        	$role = array_rand($roles);
        	$user->addRoles($roles[$role]);
        	$user->setEmail($faker->email);
        	$user->setNameuser($faker->userName); 
        	$user->setPassword($password);
        	$manager->persist($user);       	
        	$manager->persist($category);  
        } 

        for($i=1;$i<200;$i++){
        	$article 	= new Article();
        	#$images 	= ["1.jpg", "2.jpg", "3.jpg", "4.jpg", "5.jpg", "6.jpg", "7.jpg", "8.jpg", "9.jpg", "10.jpg", "11.jpg"]; 
        	#$image 		= array_rand($images);
    		$article->setPicture("0.jpg");
        	for($cat=1;$cat<rand(1,count($categories)); $cat++){
        		$category  	= array_rand($categories);
    			$article->addCategory($categories[$category]);
        	}
        	$article->setTitle($faker->name);
        	$article->setContent($faker->realText(400));
        	$article->setPublicationDate($faker->dateTime);
        	$article->setLastUpdateDate($faker->dateTime);
        	$article->setIsPublised(rand(0,1));      	
        	$manager->persist($article);        	
        }


        $comment 	= new Comment(); 

    	$manager->flush();
    }
}

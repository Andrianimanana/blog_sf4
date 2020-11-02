<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Page d\'administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('BLOG SF4');
        // yield MenuItem::linkToCrud('Ajouter article', 'fa fa-tags', Article::class)->setAction('new'); 
        yield MenuItem::linkToCrud('Article', 'fa fa-tags', Article::class); 
        // yield MenuItem::linkToCrud('Article', 'fa fa-tags', Article::class); 
    }
}

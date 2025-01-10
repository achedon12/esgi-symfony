<?php

namespace App\Controller\Admin;

use App\Entity\Discussion;
use App\Entity\Like;
use App\Entity\Message;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{

    public function __construct(private readonly string $appName, private readonly string $version)
    {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        //return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        //$adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {

        return Dashboard::new()
            ->setTitle($this->appName . ' - v' . $this->version);
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToRoute('Back to the website', 'fas fa-home', 'app_home_index'),
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            MenuItem::linkToCrud('Users', 'fas fa-user', User::class)->setController(UserCrudController::class),
            MenuItem::linkToCrud('Discussions', 'fas fa-comments', Discussion::class)->setController(DiscussionCrudController::class),
            MenuItem::linkToCrud('Messages', 'fas fa-envelope', Message::class)->setController(MessageCrudController::class),
            MenuItem::linkToCrud('Likes', 'fas fa-heart', Like::class)->setController(LikeCrudController::class),
        ];
    }
}

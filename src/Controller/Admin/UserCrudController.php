<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('firstname'),
            TextField::new('lastname'),
            TextField::new('email'),
            TextField::new('gender'),
            ArrayField::new('roles'),
            DateTimeField::new('birthdate'),
            TextField::new('score'),
            ArrayField::new('interests'),
            TextEditorField::new('bio'),
            TextField::new('image'),
            TextField::new('longitude'),
            TextField::new('latitude'),
            ArrayField::new('likes'),
            ArrayField::new('likedBy'),
            TextField::new('language'),
            DateTimeField::new('creationDate')->hideOnForm(),
            DateTimeField::new('updateDate')->hideOnForm(),
        ];
    }

}

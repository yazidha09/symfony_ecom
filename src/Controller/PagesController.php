<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class PagesController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('pages/index.html.twig', [
            'controller_name' => 'PagesController',
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig');
    }
    
    #[Route('/login', name: 'login')]
    public function login(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
    
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
    
            if ($user && password_verify($password, $user->getPassword())) {
                // Login successful - store user ID in session
                $request->getSession()->set('user_id', $user->getId());
                return $this->redirectToRoute('home');
            }
    
            // Login failed - flash error and stay on login page
            $this->addFlash('email', $email);
            $this->addFlash('error', 'Invalid credentials.');
            return $this->redirectToRoute('login');
        }
    
        return $this->render('pages/login.html.twig');
    }

    #[Route('/signup', name: 'signup')]
    public function signup(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
    
            $user = new User();
            $user->setEmail($email);
            $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
    
            $em->persist($user);
            $em->flush();
    
            return $this->redirectToRoute('login');
        }

        return $this->render('pages/signup.html.twig');

    }
    #[Route('/addtocart', name: 'addtocart')]
    public function addtocart(): Response
    {
        return $this->render('pages/addtocart.html.twig');
    }
}
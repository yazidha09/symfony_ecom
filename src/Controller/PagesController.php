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
    #[Route('/addtocart/{id}', name: 'addtocart')]
public function addtocart(Request $request, int $id = null): Response
{
    $session = $request->getSession();
    $cart = $session->get('cart', []);

    // If an ID is provided (adding a product)
    if ($id !== null) {
        // Define our products (in a real app, these would come from a database)
        $products = [
            1 => ['name' => 'Comressed Black Shirt', 'price' => 79.99, 'image' => 'https://www.teefitfashion.com/cdn/shop/files/BlackFullCompression_1080x.png?v=1744984939'],
            2 => ['name' => 'Iron Grip with Wrist Support', 'price' => 129.99, 'image' => 'https://jnbfitness.com/cdn/shop/products/image_c5d65415-fbd0-41fa-943a-d437fd1bc3a8_1024x1024.jpg?v=1549447464'],
            3 => ['name' => 'GripMaster Gloves', 'price' => 59.99, 'image' => 'https://lsmedia.linker-cdn.net/62267/2025/14047617.jpeg?d=400x400']
        ];

        if (isset($products[$id])) {
            if (isset($cart[$id])) {
                $cart[$id]['quantity']++;
            } else {
                $cart[$id] = [
                    'name' => $products[$id]['name'],
                    'price' => $products[$id]['price'],
                    'image' => $products[$id]['image'],
                    'quantity' => 1
                ];
            }
        }
        
        $session->set('cart', $cart);
        $this->addFlash('success', 'Product added to cart!');
        return $this->redirectToRoute('addtocart');
    }

    // If removing an item
    if ($request->query->has('remove')) {
        $removeId = $request->query->get('remove');
        if (isset($cart[$removeId])) {
            unset($cart[$removeId]);
            $session->set('cart', $cart);
            $this->addFlash('success', 'Product removed from cart!');
        }
        return $this->redirectToRoute('addtocart');
    }

    return $this->render('pages/addtocart.html.twig', [
        'cart' => $cart
    ]);
}
}
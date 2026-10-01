<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Promotion;
use App\Form\PromotionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PromotionController extends AbstractController
{
    #[Route(name: 'index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $promotions = $entityManager->getRepository(Promotion::class)->findAll();

        return $this->render('index.html.twig', [
            'promotions' => $promotions,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(EntityManagerInterface $entityManager, Request $request): Response
    {
        $promotion = new Promotion();

        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($promotion);
            $entityManager->flush();

            $entityManager->refresh($promotion);

            return $this->redirectToRoute('edit', [
                'promotion' => $promotion->id,
            ]);
        }

        return $this->render('new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{promotion<\d+>}', name: 'edit')]
    public function edit(EntityManagerInterface $entityManager, Request $request, Promotion $promotion): Response
    {
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($promotion);
            $entityManager->flush();

            return $this->redirectToRoute('edit', [
                'promotion' => $promotion->id,
            ]);
        }

        return $this->render('edit.html.twig', [
            'form' => $form,
            'promotion' => $promotion,
        ]);
    }

    #[Route('/{promotion<\d+>}/delete', name: 'delete', methods: ['POST'])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Promotion $promotion): Response
    {
        if ($this->isCsrfTokenValid('delete-promotion-'.$promotion->id, $request->request->getString('_token'))) {
            $entityManager->remove($promotion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('index');
    }
}

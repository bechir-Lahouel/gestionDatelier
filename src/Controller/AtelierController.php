<?php

namespace App\Controller;

use App\Entity\Atelier;
use App\Entity\Note;
use App\Form\AtelierType;
use App\Repository\AtelierRepository;
use App\Repository\NoteRepository;
use cebe\markdown\Markdown;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/atelier')]
class AtelierController extends AbstractController
{
    #[Route('/', name: 'app_atelier_index', methods: ['GET'])]
    public function index(AtelierRepository $atelierRepository): Response
    {

        $markdown = new Markdown();
        $markdown->html5 = true;
        $atellier =  $atelierRepository->findAll();
        $parsedAteliers = [];
        foreach ($atellier as $atelier) {
            $parsedAtelier =  $atelier;
            $parsedAtelier->setDescription($markdown->parse($atelier->getDescription()));
            $parsedAteliers[] = $parsedAtelier;
        }
        return $this->render('atelier/index.html.twig', [
            'ateliers' => $parsedAteliers,
            'titre' => 'Liste des ateliers',
        ]);
    }

    #[Route('/mesAteliers', name: 'app_atelier_mesAteliers', methods: ['GET'])]
    public function mesAteliers(AtelierRepository $atelierRepository): Response
    {
        //verifier si l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if (in_array('ROLE_APPRENTI', $this->getUser()->getRoles(), true)) {
            return $this->redirectToRoute('app_atelier_index');
        }

        $atellier =  $atelierRepository->findBy(['user' => $this->getUser()]);
        return $this->render('atelier/index.html.twig', [
            'ateliers' => $atellier,
            'titre' => 'Mes ateliers',
        ]);
    }

    #[Route('/mesParticipations', name: 'app_atelier_mesParticipations', methods: ['GET'])]
    public function mesParticipations(AtelierRepository $atelierRepository): Response
    {
        //verifier si l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $atellier =  $atelierRepository->findBy(['user' => $this->getUser()]);
        return $this->render('atelier/index.html.twig', [
            'ateliers' => $this->getUser()->getAtelierInscres(),
            'titre' => 'Mes participations',
        ]);
    }

    #[Route('/new', name: 'app_atelier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AtelierRepository $atelierRepository): Response
    {
        //verifier si l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if (in_array('ROLE_APPRENTI', $this->getUser()->getRoles(), true)) {
            return $this->redirectToRoute('app_atelier_index');
        }
        $atelier = new Atelier();
        $form = $this->createForm(AtelierType::class, $atelier);
        $form->handleRequest($request);
        //ajouter l'utilisateur connecté à l'atelier
        $atelier->setUser($this->getUser());

        if ($form->isSubmitted() && $form->isValid()) {
            $atelierRepository->save($atelier, true);

            return $this->redirectToRoute('app_atelier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('atelier/new.html.twig', [
            'atelier' => $atelier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_atelier_show', methods: ['GET'])]
    public function show(Atelier $atelier): Response
    {
        //get user connected
        $note = $this->getUser()->getNotes();
        $note = $note->filter(function ($note) use ($atelier) {
            return $note->getAtelier() == $atelier;
        })->first();

        return $this->render('atelier/show.html.twig', [
            'atelier' => $atelier,
            'rating' =>  $note ? $note->getValeur() : 0,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_atelier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Atelier $atelier, AtelierRepository $atelierRepository): Response
    {
        //verifier si l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if (in_array('ROLE_APPRENTI', $this->getUser()->getRoles(), true)) {
            return $this->redirectToRoute('app_atelier_index');
        }

        //verifier si l'utilisateur est le propriétaire de l'atelier
        if ($atelier->getUser() != $this->getUser()) {
            return $this->redirectToRoute('app_atelier_index');
        }

        $form = $this->createForm(AtelierType::class, $atelier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $atelierRepository->save($atelier, true);

            return $this->redirectToRoute('app_atelier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('atelier/edit.html.twig', [
            'atelier' => $atelier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_atelier_delete', methods: ['POST'])]
    public function delete(Request $request, Atelier $atelier, AtelierRepository $atelierRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if (in_array('ROLE_APPRENTI', $this->getUser()->getRoles(), true)) {
            return $this->redirectToRoute('app_atelier_index');
        }

        //verifier si l'utilisateur est le propriétaire de l'atelier
        if ($atelier->getUser() != $this->getUser()) {
            return $this->redirectToRoute('app_atelier_index');
        }

        if ($this->isCsrfTokenValid('delete'.$atelier->getId(), $request->request->get('_token'))) {
            $atelierRepository->remove($atelier, true);
        }

        return $this->redirectToRoute('app_atelier_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/noter', name: 'app_atelier_noter', methods: [ 'POST'])]
    public function noter(Request $request,Atelier $atelier,  NoteRepository $noteRepository): Response
    {
        //verifier si l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $valeur = intval($request->get('rating'));

        //verifier si l'utilisateur a deja noté l'atelier dans la base
        $note = $noteRepository->findOneBy(['atelier' => $atelier, 'user' => $this->getUser()]);
        if ($note) {
            $note->setValeur($valeur);
        }else{
            $note = new Note();
            $note->setValeur($valeur);
            $note->setAtelier($atelier);
            $note->setUser($this->getUser());
        }

        $noteRepository->save($note, true);

        return $this->render('atelier/show.html.twig', [
            'atelier' => $atelier,
            'rating' =>  $valeur,
        ]);
    }



    #[Route('/{id}/inscription', name: 'app_atelier_inscription', methods: ['GET'])]
    public function inscription(Atelier $atelier, AtelierRepository $atelierRepository): Response
    {
        //verifier si l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        //verifier si l'utilisateur est le propriétaire de l'atelier
        if ($atelier->getUser() == $this->getUser()) {
            return $this->redirectToRoute('app_atelier_index');
        }

        $atelier->addParticipant($this->getUser());
        $atelierRepository->save($atelier, true);

        return $this->redirectToRoute('app_atelier_show', ['id' => $atelier->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/desinscription', name: 'app_atelier_desinscription', methods: ['GET'])]
    public function desinscription(Atelier $atelier, AtelierRepository $atelierRepository): Response
    {
        //verifier si l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        //verifier si l'utilisateur est le propriétaire de l'atelier
        if ($atelier->getUser() == $this->getUser()) {
            return $this->redirectToRoute('app_atelier_index');
        }

        $atelier->removeParticipant($this->getUser());
        $atelierRepository->save($atelier, true);

        return $this->redirectToRoute('app_atelier_show', ['id' => $atelier->getId()], Response::HTTP_SEE_OTHER);
    }



}

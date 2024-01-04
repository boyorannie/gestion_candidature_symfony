<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Candidater;
use App\Entity\Candidature;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\DependencyInjection\ContainerInterface as DependencyInjectionContainerInterface;
// #[Route("/api", name:"api_")]
class CandidaterController extends AbstractController
{
    private $entityManager;
    protected $container;

        public function __construct(EntityManagerInterface $entityManager, DependencyInjectionContainerInterface  $container)
        {
            $this->entityManager = $entityManager;
            $this->container = $container;
        }
    
    #[Route("/api/postuler", name:"postuler_formation", methods:["POST"])]
    #[IsGranted("ROLE_CANDIDAT", message: 'Accès non autorisé')]

    public function postuler(Request $request,EntityManagerInterface $entityManager): Response
    {

        // $this->denyAccessUnlessGranted('ROLE_CANDIDAT');

        $candidat = $this->getUser();
        $data = json_decode($request->getContent(), true);

        // $user = $this->getUser(); 
        $formationId = $data['formation_id'];
        $candidatureExistante = $entityManager->getRepository(Candidature::class)->findOneBy([
            // 'user' => $user,
            'formation' => $formationId
        ]);

        if ($candidatureExistante) {
            return $this->json(['message' => 'Vous avez déjà postulé à cette formation'], Response::HTTP_BAD_REQUEST);
        }

        $formation = $entityManager->getRepository(Formation::class)->findOneBy(['id' => $data['formation_id']]);

        if (!$formation) {
            return $this->json(['message' => 'Formation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $candidature = new Candidature();
        $candidature->setCandidat($candidat);
        $candidature->setFormation($formation);

        $entityManager->persist($candidature);
        $entityManager->flush();

        return $this->json(['message' => 'Candidature enregistrée'], Response::HTTP_OK);
    }



    
    #[Route('api/lister', name: 'candidature_lister', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN", message: 'Accès non autorisé')]
    public function listerCandidatures(): Response
    {
        $entityManager = $this->entityManager;

        // Récupérer toutes les candidatures
        $candidatures = $entityManager->getRepository(Candidature::class)->findAll();
    
        $candidaturesDetails = [];
        foreach ($candidatures as $candidature) {
            $candidatureDetails = [
                'id' => $candidature->getId(),
                'user_details' => [
                    'id' => $candidature->getCandidat()->getId(),
                    'email'=>$candidature->getCandidat()->getEmail()
                ],
                'formation_details' => [
                    'id' => $candidature->getFormation()->getId(),
                    'nom'=>$candidature->getFormation()->getNom()
                ]
            ];
    
            $candidaturesDetails[] = $candidatureDetails;
        }
    
        return $this->json($candidaturesDetails);
    }

    #[Route('api/accepter/{id}', name: 'candidature_acceptee', methods: ['PUT'])]
    #[IsGranted("ROLE_ADMIN", message: 'Accès non autorisée')]
    public function accepterCandidature(Candidature $id): Response
    {
        $entityManager =  $this->entityManager;
    
        if (!$id) {
            return $this->json(['message' => 'Candidature non trouvée'], Response::HTTP_NOT_FOUND);
        }
    
        // Modifier le statut de la candidature
        $id->setStatut('accepté');
    
        // Sauvegarder les modifications
        $entityManager->flush();
    
        return $this->json(['message' => 'Candidature acceptée']);
    }

    #[Route('api/lister_acceptees', name: 'candidature_liste_acceptees', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN", message: 'Accès non autorisé')]
    public function listerCandidaturesAcceptees(): Response
    {
        $entityManager = $this->entityManager;
    
        // Récupérer toutes les candidatures ayant le statut "accepté"
        $candidaturesAcceptees = $entityManager->getRepository(Candidature::class)->findBy(['statut' => 'accepté']);
    
        $candidaturesDetails = [];
        foreach ($candidaturesAcceptees as $candidature) {
            $candidatureDetails = [
                'id' => $candidature->getId(),
                'statut' => $candidature->getStatut(),
                'user_details' => [
                    'id' => $candidature->getCandidat()->getId(),
                    'email'=>$candidature->getCandidat()->getEmail()
                    
                ],
                'formation_details' => [
                    'id' => $candidature->getFormation()->getId(),
                    'libeller'=>$candidature->getFormation()->getNom()

                ]
            ];
    
            $candidaturesDetails[] = $candidatureDetails;
        }
    
        return $this->json($candidaturesDetails);
    }

    #[Route('api/lister_refusees', name: 'candidature_liste_refusees', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN", message: 'Accès non autorisé')]
    public function listerCandidaturesRefusees(): Response
    {
        $entityManager = $this->entityManager;
    
        // Récupérer toutes les candidatures ayant le statut "accepté"
        $candidaturesAcceptees = $entityManager->getRepository(Candidature::class)->findBy(['statut' => 'refusé']);
    
        $candidaturesDetails = [];
        foreach ($candidaturesAcceptees as $candidature) {
            $candidatureDetails = [
                'id' => $candidature->getId(),
                'status' => $candidature->getStatut(),
                'user_details' => [
                    'id' => $candidature->getCandidat()->getId(),
                    'email'=>$candidature->getCandidat()->getEmail()
                    
                ],
                'formation_details' => [
                    'id' => $candidature->getFormation()->getId(),
                    'libeller'=>$candidature->getFormation()->getNom()

                ]
            ];
    
            $candidaturesDetails[] = $candidatureDetails;
        }
    
        return $this->json($candidaturesDetails);
        
    }
}

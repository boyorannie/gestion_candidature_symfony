<?php
 
 namespace App\Controller;

 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 use Symfony\Component\HttpFoundation\JsonResponse;
 use Symfony\Component\Routing\Annotation\Route;
 use Symfony\Component\HttpFoundation\Request;
 use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
 use Doctrine\Persistence\ManagerRegistry;
 use Doctrine\ORM\EntityManagerInterface; // Import de EntityManagerInterface
 use Symfony\Component\Validator\Validator\ValidatorInterface; // Import de ValidatorInterface
 use Symfony\Component\Validator\Constraints as Assert;
 use App\Entity\User;
#[Route('/api', name: 'api_')]
class RegistrationController extends AbstractController
{
      
        #[Route('/inscrire', name: 'inscrire_candidat', methods: ['POST'])]
        public function register(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator)
        {
            $data = json_decode($request->getContent(), true);

            $constraints = new Assert\Collection([
                'nom' => [new Assert\NotBlank(), new Assert\Length(['min' => 4])],
                'prenom' => [new Assert\NotBlank(), new Assert\Length(['min' => 4])],
                'email' => [new Assert\NotBlank(), new Assert\Email()],
                'password' => [new Assert\NotBlank()],
                'age' => [new Assert\NotBlank()],
                'adresse' => [new Assert\NotBlank()],
                'niveauEtude' => [new Assert\NotBlank()],
                'dateNaissance' => [new Assert\NotBlank()],
            ]);

            $violations = $validator->validate($data, $constraints);
            if (count($violations) > 0) {
                return $this->json(['errors' => (string) $violations], JsonResponse::HTTP_BAD_REQUEST);
            }

            $user = new User();
            $user->setNom($data['nom']);
            $user->setPrenom($data['prenom']);
            $user->setEmail($data['email']);
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
            $user->setAdresse($data['adresse']);
            $user->setAge($data['age']); // Correction ici, setAge pour l'âge
            $user->setNiveauEtude($data['niveauEtude']);
            $dateNaissance = new \DateTime($data['dateNaissance']);
          

            $dateFormatee =  $dateNaissance->format('Y-m-d');
            //dd($dateFormatee);
            if ($dateNaissance instanceof \DateTime) {

              
                    // Formatez selon le format requis
                    $user->setDateNaissance($dateFormatee );
                    $user->setRoles(['ROLE_CANDIDAT']);
                    
                    $em->persist($user);
                    $em->flush();

                    return $this->json(['message' => 'Inscription Candidat réussi'], JsonResponse::HTTP_CREATED);
                   
              }else{
                return $this->json(['message' => 'la date de naissance n\'est pas bon, ton maudiat '], 422);


              }
    }

    #[Route('/inscrire-admin', name: 'inscrire_admin', methods: ['POST'])]
    public function ajouterUtilisateurAdmin(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        $constraints = new Assert\Collection([
            'nom' => [new Assert\NotBlank(), new Assert\Length(['min' => 2]), new Assert\Regex('/^[a-zA-Z]+$/')],
            'prenom' => [new Assert\NotBlank(), new Assert\Length(['min' => 4]), new Assert\Regex('/^[a-zA-Z]+$/')],
            'email' => [new Assert\NotBlank()],
            'adresse' => [new Assert\NotBlank()],
            'password' => [new Assert\NotBlank()],
    
        ]);
    
        $violations = $validator->validate($data, $constraints);
        if (count($violations) > 0) {
            return $this->json(['errors' => (string) $violations], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        $user = new User();
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setAdresse($data['adresse']);
        $user->setRoles(['ROLE_ADMIN']); 
    
        $em->persist($user);
        $em->flush();
    
        return $this->json(['message' => 'Inscription Admin réussi'], JsonResponse::HTTP_CREATED);
    }
}
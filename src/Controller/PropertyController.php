<?php

namespace App\Controller;

use App\Entity\Property;
use App\Entity\Appointment;
use App\Form\AppointementType;
use App\Form\FilterType;
use App\Repository\PropertyRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

class PropertyController extends AbstractController
{
    private PropertyRepository $repository;
    private ObjectManager $om;

    public function __construct(ManagerRegistry $manager)
    {
        $this->repository = $manager->getRepository(Property::class);
        $this->om = $manager->getManager();
    }

    #[Route('/', name: 'app_home', methods:["GET"])]
    public function home(RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $session->clear();
        $session->set('panier', [
            [
                "name" => "pomme",
                "quantité" => 3,
                "prixTotal" => 3
            ]
            ]);
        dump($session->get('panier'));
        return $this->render('property/home.html.twig', [
            'properties' => $this->repository->findBy(['available' => true], ['id' => "DESC"], 5)
        ]);
    }

    #[Route('/properties', name:"app_property", methods:["GET", "POST"])]
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $form = $this->createForm(FilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $request->get('filter');
            dump($filters['transactionType']);
            $properties = $this->repository->filter(
                minSize: $filters['minSize'],
                maxSize: $filters['maxSize'],
                minRooms: $filters['minRooms'],
                maxRooms: $filters['maxRooms'],
                minPrice: $filters['minPrice'],
                maxPrice: $filters['maxPrice'],
                transactionType: $filters['transactionType'] >= 0 ? boolval($filters['transactionType']): null,
                propertyType: $filters['propertyType'] >= 0 ? intval($filters['propertyType']): null
            );
        } else {
            $properties = $this->repository->findBy(['available' => true]);
        }

        $pagination = $paginator->paginate(
            $properties,
            $request->query->getInt('page', 1),
            8
        );

        return $this->renderForm('property/index.html.twig', [
            'pagination' => $pagination,
            'filterForm' => $form
        ]);
    }

    #[Route("/property/{id}", name:"show_property", requirements:['id' => "\d+"], methods:["GET", "POST"])]
    public function show(int $id, Request $request): Response
    {
        $property = $this->repository->find($id);
        if (!$property) {
            $this->addFlash('danger', "Bien non trouvé");
            return $this->redirectToRoute('app_home');
        }

        $appointment = new Appointment;
        $contactForm = $this->createForm(AppointementType::class, $appointment);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $appointment->setProperty($property);
            $this->om->persist($appointment);
            $this->om->flush();
            return $this->redirectToRoute('show_property', ['id' => $property->getId()]);
        }

        return $this->renderForm('property/show.html.twig', [
            'property' => $property,
            'contactForm' => $contactForm
        ]);
    }
}

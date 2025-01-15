<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Repository\OfferRepository;
use Psr\Log\LoggerInterface;
use Stripe;
use App\Event\UserOfferChangedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/offer', name: 'app_offer_')]
class OfferController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface   $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TranslatorInterface      $translator,
        private readonly LoggerInterface $logger,
        private readonly OfferRepository $offerRepository
    )
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $offers = $this->entityManager->getRepository(Offer::class)->findAll();
        return $this->render('profile/userOffer/offer.html.twig', [
            'offers' => $offers
        ]);
    }

    #[Route('/pay_offer/{offerId}', name: 'pay')]
    public function payOffer(Request $request, int $offerId): Response
    {
        $this->logger->info('------------------[OfferController] payOffer: sdf ------------------');
        $offer = $this->offerRepository->find($offerId);

        if (!$offer) {
            throw $this->createNotFoundException($this->translator->trans('exception.offer.not_found'));
        }
        $this->logger->info('------------------[OfferController] payOffer: ' . $offerId . '------------------');
        return $this->render('profile/userOffer/pay_offer.html.twig', [
            'offer' => $offer,
            'stripe_key' => $_ENV["STRIPE_KEY"],
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    #[Route('/create-charge', name: 'charge', methods: ['POST'])]
    public function createCharge(Request $request): Response
    {
        $offer = $this->entityManager->getRepository(Offer::class)->find($request->request->get('offer_id'));

        Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET"]);
        Stripe\Charge::create([
            "amount" => $offer->getPrice() * 100,
            "currency" => "usd",
            "source" => $request->request->get('stripeToken'),
            "description" => "Payment Test"
        ]);

        $this->changeOfferForUser($offer, $this->getUser());

        $this->addFlash(
            'success',
            'Payment Successful!'
        );
        return $this->redirectToRoute('app_offer_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/change_offer', name: 'change')]
    public function changeOffer(Request $request): Response
    {
        $offerId = (int)$request->request->get('offer_id');
        $offer = $this->entityManager->getRepository(Offer::class)->find($offerId);
        $user = $this->getUser();

        if ($offerId !== 0) {
            $this->changeOfferForUser($offer, $user);
            $this->addFlash('success', $this->translator->trans('flash.offer.changed'));
        }

        return $this->redirectToRoute('app_offer_index');
    }

    private function changeOfferForUser($offer, $user): void
    {
        $user->setOffer($offer);
        $offer->addUser($user);

        $event = new UserOfferChangedEvent($user, $offer);
        $this->eventDispatcher->dispatch($event, UserOfferChangedEvent::NAME);

        $this->entityManager->flush();
    }
}

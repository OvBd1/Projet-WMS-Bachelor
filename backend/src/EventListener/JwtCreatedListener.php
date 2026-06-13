<?php

namespace App\EventListener;

use App\Entity\Utilisateur;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_created')]
class JwtCreatedListener
{
    public function __invoke(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof Utilisateur) {
            return;
        }

        $payload = $event->getData();
        $payload['id']     = $user->getId();
        $payload['nom']    = $user->getNom();
        $payload['prenom'] = $user->getPrenom();
        $event->setData($payload);
    }
}

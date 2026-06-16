<?php

namespace App\EventListener;

use App\Service\DossierContext;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::CONTROLLER)]
class DossierFilterActivator
{
    public function __construct(private DossierContext $dossierContext) {}

    public function __invoke(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->dossierContext->getCurrent();
    }
}

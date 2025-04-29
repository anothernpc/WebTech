<?php
declare(strict_types=1);

use templates\TemplateEngine;

require_once __DIR__ . '/../services/EventService.php';
require_once __DIR__ . '/../templates/TemplateEngine.php';
require_once __DIR__ . '/../services/CartService.php';

class EventsController
{
    private EventsService $eventsService;
    private CartService $cartService;

    public function __construct()
    {
        $this->eventsService = new EventsService(new TemplateEngine());
        $this->cartService = new CartService();
    }

    public function listEvents(array $input): void
    {
        $events = $this->eventsService->getUpcomingEvents();
        echo $this->eventsService->render('/var/www/WebTech/templates/events-list.php', [
            'events' => $events,
            'cartCount' => $this->cartService->getCartCount()
        ]);
    }

    public function showEvent(array $input): void
    {
        if (empty($input['event_id'])) {
            http_response_code(400);
            echo "Missing event ID";
            return;
        }

        $event = $this->eventsService->getEvent((int)$input['event_id']);
        if (!$event) {
            http_response_code(404);
            echo "Event not found";
            return;
        }

        echo $this->eventsService->render('/var/www/WebTech/templates/events-show.php', [
            'event' => $event,
            'cartCount' => $this->cartService->getCartCount()
        ]);
    }

    public function addToCart(array $input): void
    {
        if (empty($input['event_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing event ID']);
            return;
        }

        $cartService = new CartService();
        $success = $cartService->addToCart((int)$input['event_id']);

        echo json_encode([
            'success' => $success,
            'cartCount' => $cartService->getCartCount()
        ]);
    }
}
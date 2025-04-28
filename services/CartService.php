<?php
declare(strict_types=1);

use templates\TemplateEngine;

class CartService {
    private EventsService $eventsService;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['cart'] = $_SESSION['cart'] ?? [];
        $this->eventsService = new EventsService(new TemplateEngine());
    }

    public function addToCart(int $eventId, int $quantity = 1): bool {
        if ($this->eventsService->getEvent($eventId)) {
            $_SESSION['cart'][$eventId] = ($_SESSION['cart'][$eventId] ?? 0) + $quantity;
            return true;
        }
        return false;
    }
    public function removeItem(int $eventId): bool {
        if (isset($_SESSION['cart'][$eventId])) {
            unset($_SESSION['cart'][$eventId]);
            return true;
        }
        return false;
    }

    public function getCartItems(): array {
        $items = [];
        foreach ($_SESSION['cart'] as $eventId => $quantity) {
            if ($event = $this->eventsService->getEvent((int)$eventId)) {
                $items[] = [
                    'id' => $event['id'],
                    'title' => $event['title'],
                    'date' => $event['date'],
                    'price' => $event['price'],
                    'quantity' => $quantity,
                    'subtotal' => $event['price'] * $quantity
                ];
            }
        }
        return $items;
    }

    public function getCartTotal(): float {
        return array_reduce($this->getCartItems(),
            fn($total, $item) => $total + $item['subtotal'],
            0.0
        );
    }
    public function getCartCount(): int {
        return array_sum($_SESSION['cart']);
    }
}
<?php
declare(strict_types=1);

require_once __DIR__ . '/../repository/CartRepository.php';
require_once __DIR__ . '/../services/EventService.php';

class CartService
{
    private CartRepository $cartRepository;

    public function __construct()
    {
        $env = parse_ini_file(__DIR__ . '/../.env');
        $pdo = new PDO("mysql:host={$env['DB_HOST']};dbname={$env['DB_DATABASE']}", $env['DB_USERNAME'], $env['DB_PASSWORD']);
        $this->cartRepository = new CartRepository($pdo);
    }

    public function addToCart(int $eventId, int $quantity = 1): bool
    {
        if (!isset($_SESSION['user'])) {
            return false;
        }
        $cartItem = new CartEntity(
            null,
            $_SESSION['user']['id'],
            $eventId,
            new DateTime()
        );

        return $this->cartRepository->save($cartItem);
    }

    public function removeItem(int $eventId): bool
    {
        if ($_SESSION['user']['id'] === null) {
            return false;
        }

        $item = $this->cartRepository->findByUserAndEvent($_SESSION['user']['id'], $eventId);
        if ($item && $item->getId()) {
            return $this->cartRepository->delete($item->getId());
        }

        return false;
    }

    public function getCartItems(EventsService $eventsService = null): array
    {
        if ($_SESSION['user']['id'] === null) {
            return [];
        }

        $cartItems = $this->cartRepository->findByUser($_SESSION['user']['id']);

        if ($eventsService === null) {
            return $cartItems;
        }

        $enrichedItems = [];
        foreach ($cartItems as $item) {
            $event = $eventsService->getEvent($item['event_id']);
            if ($event) {
                $enrichedItems[] = [
                    'id' => $item['id'],
                    'event_id' => $item['event_id'],
                    'title' => $event['title'],
                    'date' => $event['date'],
                    'price' => $event['price'],
                    'quantity' => 1,
                    'subtotal' => $event['price'],
                    'added_at' => $item['added_at']
                ];
            }
        }

        return $enrichedItems;
    }

    public function getCartTotal(EventsService $eventsService = null): float
    {
        $items = $this->getCartItems($eventsService);
        $total = 0.0;

        foreach ($items as $item) {
            $total += $item['price'] * ($item['quantity'] ?? 1);
        }

        return $total;
    }

    public function getCartCount(): int
    {
        if (!isset($_SESSION['user'])) {
            return 0;
        }

        $items = $this->cartRepository->findByUser($_SESSION['user']['id']);
        return count($items);
    }

    public function clearCart(): bool
    {
        if ($_SESSION['user']['id'] === null) {
            return false;
        }

        $items = $this->cartRepository->findByUser($_SESSION['user']['id']);
        $success = true;

        foreach ($items as $item) {
            if (!$this->cartRepository->delete($item['id'])) {
                $success = false;
            }
        }

        return $success;
    }

    private function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
}
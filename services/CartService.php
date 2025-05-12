<?php
declare(strict_types=1);

require_once __DIR__ . '/../model/CartEntity.php';
require_once __DIR__ . '/../repository/CartRepository.php';

class CartService
{
    private EventsService $eventsService;
    private CartRepository $cartRepository;

    public function __construct()
    {
        $this->eventsService = new EventsService(new TemplateEngine());
        $pdo = new PDO(
            "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']}",
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD']
        );
        $this->cartRepository = new CartRepository($pdo);
    }

    public function addToCart(int $eventId, int $quantity = 1): bool
    {
        $event = $this->eventsService->getEvent($eventId);
        if (!$event) {
            return false;
        }

        $existingItem = $this->cartRepository->findByTitle($event['title']);
        if ($existingItem) {
            $cartEntity = new CartEntity(
                $existingItem[0]['id'],
                $existingItem[0]['title'],
                $existingItem[0]['date'],
                (int)$existingItem[0]['price'],
                (int)$existingItem[0]['quantity'] + $quantity,
                (int)$existingItem[0]['price'] * ((int)$existingItem[0]['quantity'] + $quantity)
            );
            return $this->cartRepository->save($cartEntity);
        }

        $cartEntity = new CartEntity(
            null,
            $event['title'],
            $event['date'],
            (int)$event['price'],
            $quantity,
            (int)$event['price'] * $quantity
        );

        return $this->cartRepository->save($cartEntity);
    }

    public function removeItem(int $eventId): bool
    {
        $event = $this->eventsService->getEvent($eventId);
        if (!$event) {
            return false;
        }

        return $this->cartRepository->delete($event['id']);
    }

    public function getCartItems(): array
    {
        return $this->cartRepository->findAll();
    }

    public function getCartTotal(): float
    {
        $items = $this->cartRepository->findAll();
        return array_reduce($items,
            fn($total, $item) => $total + $item['subtotal'],
            0.0
        );
    }

    public function getCartCount(): int
    {
        $items = $this->cartRepository->findAll();
        return array_reduce($items,
            fn($count, $item) => $count + $item['quantity'],
            0
        );
    }

    public function clearCart(): void
    {
        $items = $this->cartRepository->findAll();
        foreach ($items as $item) {
            $this->cartRepository->delete($item['id']);
        }
    }
}
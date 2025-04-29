<?php
declare(strict_types=1);

use templates\TemplateEngine;

class CartService
{
    private EventsService $eventsService;
    private string $cartFile;
    private array $cart = [];

    public function __construct()
    {
        $this->eventsService = new EventsService(new TemplateEngine());
        $this->cartFile = __DIR__ . '/../config/cart.php';
        $this->loadCart();
    }

    private function loadCart(): void
    {
        if (file_exists($this->cartFile)) {
            $this->cart = include $this->cartFile;
        } else {
            $this->cart = [];
            $this->saveCart();
        }
    }

    private function saveCart(): void
    {
        $content = "<?php\nreturn " . var_export($this->cart, true) . ";\n";

        if (!is_writable(dirname($this->cartFile))) {
            throw new RuntimeException('Config directory is not writable');
        }

        $result = file_put_contents($this->cartFile, $content, LOCK_EX);

        if ($result === false) {
            throw new RuntimeException('Failed to save cart data');
        }
    }

    public function addToCart(int $eventId, int $quantity = 1): bool
    {
        if ($this->eventsService->getEvent($eventId)) {
            $this->cart[$eventId] = ($this->cart[$eventId] ?? 0) + $quantity;
            $this->saveCart();
            return true;
        }
        return false;
    }

    public function removeItem(int $eventId): bool
    {
        if (isset($this->cart[$eventId])) {
            unset($this->cart[$eventId]);
            $this->saveCart();
            return true;
        }
        return false;
    }

    public function getCartItems(): array
    {
        $items = [];
        foreach ($this->cart as $eventId => $quantity) {
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

    public function getCartTotal(): float
    {
        return array_reduce($this->getCartItems(),
            fn($total, $item) => $total + $item['subtotal'],
            0.0
        );
    }

    public function getCartCount(): int
    {
        return array_sum($this->cart);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->saveCart();
    }
}
<?php
declare(strict_types=1);

require_once __DIR__ . '/../services/CartService.php';
require_once __DIR__ . '/../services/EventService.php';
require_once __DIR__ . '/../TemplateEngine.php';

class CartController
{
    private EventsService $eventsService;
    private CartService $cartService;

    public function __construct()
    {
        $this->eventsService = new EventsService(new TemplateEngine());
        $this->cartService = new CartService();
    }

    public function viewCart(array $input): void
    {
        echo $this->eventsService->render('/var/www/WebTech/templates/cart-view.html', [
            'events' => $this->cartService->getCartItems($this->eventsService),
            'total' => $this->cartService->getCartTotal($this->eventsService),
            'cartCount' => $this->cartService->getCartCount()
        ]);
    }

    public function getCartData(array $input): void
    {
        $cartService = new CartService();
        echo json_encode([
            'events' => $cartService->getCartItems(),
            'total' => $cartService->getCartTotal(),
            'cartCount' => $cartService->getCartCount()
        ]);
    }

    public function removeFromCart(array $input): void
    {
        $cartService = new CartService();
        $cartService->removeItem((int)$input['event_id']);
        $this->getCartData($input);
    }

    public function getCartCount(array $input): void
    {
        echo json_encode(['count' => $this->cartService->getCartCount()]);
    }

}
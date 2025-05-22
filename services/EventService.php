<?php
declare(strict_types=1);

require_once __DIR__ . '/../model/EventEntity.php';
require_once __DIR__ . '/../repository/EventRepository.php';
class EventsService
{
    private TemplateEngine $templateEngine;
    private mixed $events;
    private EventRepository $eventRepository;
    protected PDO $pdo;

    public function __construct(TemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
        $env = parse_ini_file(__DIR__ . '/../.env');
        $this->pdo = new PDO("mysql:host={$env['DB_HOST']};dbname={$env['DB_DATABASE']}", $env['DB_USERNAME'], $env['DB_PASSWORD']);
        $this->eventRepository = new EventRepository($this->pdo);
    }

    public function getAllEvents(): array
    {
        return $this->eventRepository->findAll();
    }

    public function getUpcomingEvents(int $limit = 10): array
    {
        return $this->eventRepository->findUpcomingEvents($limit);
    }

    public function searchEvents(string $query): array
    {
        return $this->eventRepository->findByTitle($query);
    }

    public function getEvent(int $id): ?array
    {
        $event = $this->eventRepository->find($id);

        if ($event === null) {
            return null;
        }

        return [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'date' => $event->getDate(),
            'price' => $event->getPrice(),
            'image' => $event->getImage()
        ];
    }

   public function createEvent(
        string  $title,
        string  $description,
        string  $date,
        int     $price,
        ?string $image = null
    ): EventEntity
    {
        $event = new EventEntity(null, $title, $description, $date, $price, $image);
        $this->eventRepository->save($event);
        return $event;
    }

    /*public function updateEvent(
        int     $id,
        string  $title,
        string  $description,
        string  $date,
        int     $price,
        ?string $image = null
    ): bool
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            throw new RuntimeException("Event with ID $id not found");
        }

        $event->setTitle($title);
        $event->setDescription($description);
        $event->setDate($date);
        $event->setPrice($price);
        $event->setImage($image);

        return $this->eventRepository->save($event);
    } */

    public function deleteEvent(int $id): bool
    {
        return $this->eventRepository->delete($id);
    }

    public function render(string $template, array $data): string
    {
        return $this->templateEngine->render($template, $data);
    }
}


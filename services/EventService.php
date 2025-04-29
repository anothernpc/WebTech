<?php
declare(strict_types=1);

use templates\TemplateEngine;

class EventsService
{
    private TemplateEngine $templateEngine;
    private mixed $events;

    public function __construct(TemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
        $this->events = require __DIR__ . '/../config/events.php';
    }

    public function getUpcomingEvents(): array
    {
        return array_filter($this->events, function ($event) {
            return strtotime($event['date']) >= time();
        });
    }

    public function getEvent(int $id): ?array
    {
        foreach ($this->events as $event) {
            if ($event['id'] === $id) {
                return $event;
            }
        }
        return null;
    }

    public function render(string $template, array $data): string
    {
        return $this->templateEngine->render($template, $data);
    }
}
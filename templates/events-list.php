<div class="event-list">

    @foreach($events as $event)
    <div class="event-card">
        <h2>{{ echo htmlspecialchars($event['title']); }}</h2>
        <div class="event-meta">
            <span class="event-date">{{ echo date('F j, Y', strtotime($event['date'])); }}</span>
            <span class="event-price">${{ echo number_format($event['price'], 2); }}</span>
        </div>
        <p class="event-description">{{ echo htmlspecialchars($event['description']); }}</p>

        <div class="event-actions">
            <a href="/events/view?id={{ echo $event['id']; }}" class="view-details">View Details</a>
            <button class="add-to-cart"
                    data-event-id="{{ echo $event['id']; }}"
                    data-event-title="{{ echo htmlspecialchars($event['title']); }}">
                Add to Cart
            </button>
        </div>
    </div>
    @endforeach
</div>
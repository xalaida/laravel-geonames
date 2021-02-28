<?php

namespace Nevadskiy\Geonames\Support\Traits;

trait Events
{
    /**
     * The event listeners stack.
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * Subscribe the callback on the given event name.
     */
    public function onEvent(string $event, callable $callback): self
    {
        $this->listeners[$event][] = $callback;

        return $this;
    }

    /**
     * Fire the event with the given payload.
     */
    protected function fireEvent(string $event, array $payload = []): void
    {
        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $callback) {
                $callback(...$payload);
            }
        }
    }
}

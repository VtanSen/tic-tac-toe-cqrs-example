<?php

declare(strict_types=1);

namespace Kev\Shared\Domain\Bus\Event;

use Kev\Infraestructure\Bus\Event\Guard\DomainEventGuard;
use Kev\Shared\Domain\Bus\Message;
use Kev\Types\ValueObject\Uuid;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

abstract class DomainEvent extends Message
{
    private $eventId;
    private $aggregateId;
    private $data;
    private $occurredOn;

    public function __construct(
        string $aggregateId,
        array $data = [],
        string $eventId = null,
        string $occurredOn = null
    ) {
        $eventId = $eventId ?: Uuid::random()->value();

        parent::__construct(new Uuid($eventId));

        $this->eventId = $eventId;
        $this->guardAggregateId($aggregateId);
        DomainEventGuard::guard($data, $this->rules(), get_called_class());

        $this->aggregateId = $aggregateId;
        $this->data        = $data;
        $this->occurredOn  = $occurredOn ?: (string) (new DateTimeImmutable())->getTimestamp();
    }

    abstract public static function eventName(): string;

    abstract protected function rules() : array;

    public function eventId() : string
    {
        return $this->eventId;
    }

    public function aggregateId()
    {
        return $this->aggregateId;
    }

    public function data() : array
    {
        return $this->data;
    }

    public function occurredOn() : string
    {
        return $this->occurredOn;
    }

    public function __call($method, $args)
    {
        $attributeName = $method;
        if (0 === strpos($method, 'is')) {
            $attributeName = lcfirst(substr($method, 2));
        }

        if (0 === strpos($method, 'has')) {
            $attributeName = lcfirst(substr($method, 3));
        }

        if (isset($this->data[$attributeName])) {
            return $this->data[$attributeName];
        }

        throw new RuntimeException(sprintf('The method "%s" does not exist.', $method));
    }

    private function guardAggregateId($aggregateId)
    {
        if (!is_string($aggregateId) && !is_int($aggregateId)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The Aggregate Id <%s> in <%s> is not valid, should be int or string.',
                    var_export($aggregateId, true),
                    get_class($this)
                )
            );
        }
    }
}

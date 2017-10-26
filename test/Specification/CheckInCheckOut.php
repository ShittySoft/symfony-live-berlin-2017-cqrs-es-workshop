<?php

namespace Specification;

use Behat\Behat\Context\Context;
use Building\Domain\Aggregate\Building;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Rhumsaa\Uuid\Uuid;

final class CheckInCheckOut implements Context
{
    /**
     * @var AggregateChanged[]
     */
    private $pastEvents = [];

    /**
     * @var AggregateChanged[]|null
     */
    private $recordedEvents;

    /**
     * @var Building|null
     */
    private $building;

    /**
     * @Given a building was registered
     */
    public function a_building_was_registered() : void
    {
        $this->recordPastEvent(NewBuildingWasRegistered::occur(
            Uuid::uuid4()->toString(),
            ['name' => 'A place']
        ));
    }

    /**
     * @When the user checks into the building
     */
    public function the_user_checks_into_the_building() : void
    {
        $this->building()->checkInUser('a user');
    }

    /**
     * @Then the user was checked into the building
     */
    public function the_user_was_checked_into_the_building() : void
    {
        if (! $this->popNextRecordedEvent() instanceof UserCheckedIn) {
            throw new \UnexpectedValueException('Not checked in');
        }
    }

    private function building() : Building
    {
        if ($this->building) {
            return $this->building;
        }

        return $this->building = (new AggregateTranslator())->reconstituteAggregateFromHistory(
            AggregateType::fromAggregateRootClass(Building::class),
            new \ArrayIterator($this->pastEvents)
        );
    }

    private function recordPastEvent(AggregateChanged $pastEvent) : void
    {
        $this->pastEvents[] = $pastEvent;
    }

    private function popNextRecordedEvent() : AggregateChanged
    {
        if (null !== $this->recordedEvents) {
            return \array_shift($this->recordedEvents);
        }

        $this->recordedEvents = (new AggregateTranslator())->extractPendingStreamEvents($this->building());

        return \array_shift($this->recordedEvents);
    }
}

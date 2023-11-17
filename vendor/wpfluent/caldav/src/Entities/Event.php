<?php

namespace FluentBooking\Package\CalDav\Entities;

use FluentBooking\Framework\Support\DateTime;

class Event implements \JsonSerializable
{
	protected $calendar = null;

	protected $icalEvent = [];
	
	protected $meta = [];

	protected $dateTimeFields = [
		'dtstart',
		'dtend',
		'created',
		'dtstamp',
		'last_modified'
	];

	public function __construct($event, array $meta)
	{
		$this->meta = $meta;
		$this->icalEvent = $event;
		$this->convertDateTimeStringsToDateTimeObject();
	}

	protected function convertDateTimeStringsToDateTimeObject()
	{
		foreach ($this->dateTimeFields as $field) {
			$this->icalEvent->{$field} = DateTime::create($this->icalEvent->{$field});
		}
	}

	public function getIcalEvent()
	{
		return $this->icalEvent;
	}

	public function __get($key)
	{
		if (isset($this->meta[$key])) {
			return $this->meta[$key];
		}

		return $this->icalEvent->{$key};
	}

	public function __set($key, $value)
	{
		$this->icalEvent->{$key} = $value;
	}

	public function save()
	{
		if ($this->calendar->addEvent($this->icalEvent)) {
			return $this->calendar->getEvent($this->icalEvent->getUid());
		}
	}

	public function setCalendar($calendar)
	{
		$this->calendar = $calendar;
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return $this->icalEvent;
	}
}

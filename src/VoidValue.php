<?php declare(strict_types=1);

namespace JsonPointer;

final class VoidValue
{
	/** @var array|null */
	protected $owner;
	/** @var string|null */
	protected $target;

	public function __construct(array $owner = null, string $target = null)
	{
		$this->owner = $owner;
		$this->target = $target;
	}

	public function getOwner(): ?array
	{
		return $this->owner;
	}

	public function getTarget(): ?string
	{
		return $this->target;
	}
}

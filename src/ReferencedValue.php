<?php declare(strict_types=1);

namespace JsonPointer;

use JsonPointer\Exceptions\Reference;

final class ReferencedValue
{
	/** @var array|VoidValue */
	private $owner;
	/** @var string|null */
	private $token;
	/** @var ArrayAccessor|null */
	private $accessor;

	public function __construct($owner, ?string $token = null, ArrayAccessor $accessor = null)
	{
		$this->owner = $owner;
		$this->token = $token;
		$this->accessor = $accessor;

		$this->assertPropertiesAccessible();

		$this->assertAccessorCovers();
	}

	public function hasValue(): void
	{
		$this->assertElementExists();
	}

	public function getValue()
	{
		$this->assertElementExists();

		if ($this->token === null) {
			return $this->owner;
		}

		return $this->accessor->getValue($this->owner, $this->token);
	}

	protected function assertAccessorCovers(): void
	{
		if ($this->accessor === null) {
			return;
		}

		if (!$this->accessor->covers($this->owner)) {
			throw Reference::cantUseAccessor();
		}
	}

	protected function assertPropertiesAccessible(): void
	{
		if ($this->accessor === null && $this->token !== null) {
			throw new Reference('Properties are not accessible');
		}
	}

	private function assertElementExists(): void
	{
		$this->assertOwnerExists();

		if ($this->token === null) {
			return;
		}

		if (!$this->accessor->hasValue($this->owner, $this->token)) {
			throw Reference::elementNotFound();
		}
	}

	private function assertOwnerExists(): void
	{
		if ($this->owner instanceof VoidValue) {
			throw Reference::notFound();
		}
	}
}
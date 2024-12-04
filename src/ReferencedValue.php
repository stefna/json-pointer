<?php declare(strict_types=1);

namespace JsonPointer;

use JsonPointer\Exceptions\Reference;

final class ReferencedValue
{
	public function __construct(
		/** @var array<string, mixed>|VoidValue */
		private array|VoidValue $owner,
		private readonly ?string $token = null,
		private readonly ?ArrayAccessor $accessor = null,
	) {
		$this->assertPropertiesAccessible();
		$this->assertAccessorCovers();
	}

	public function hasValue(): void
	{
		$this->assertElementExists();
	}

	/**
	 * @return mixed|VoidValue
	 */
	public function getValue(): mixed
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

	/**
	 * @phpstan-assert array<string, mixed> $this->owner
	 * @phpstan-assert ArrayAccessor $this->accessor
	 */
	private function assertElementExists(): void
	{
		$this->assertOwnerExists();

		if ($this->token === null) {
			return;
		}

		if (!$this->accessor?->hasValue($this->owner, $this->token)) {
			throw Reference::elementNotFound($this->token);
		}
	}

	/**
	 * @phpstan-assert array<string, mixed> $this->owner
	 */
	private function assertOwnerExists(): void
	{
		if ($this->owner instanceof VoidValue) {
			throw Reference::notFound();
		}
	}
}

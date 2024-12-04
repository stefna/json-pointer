<?php declare(strict_types=1);

namespace JsonPointer;

final readonly class VoidValue
{
	public function __construct(
		/** @var array<mixed> */
		public ?array $owner = null,
		public ?string $target = null,
	) {}
}

<?php declare(strict_types=1);

namespace JsonPointer;

interface WritableDocument
{
	/**
	 * Return list of all $ref elements in document
	 *
	 * Path will point to parent element from $ref this is to make it easy to replace the $ref
	 *
	 * @return array<string, string>
	 */
	public function findAllReferences(): array;

	public function set(string $path, mixed $value): void;

	public function add(string $path, mixed $value): void;
}

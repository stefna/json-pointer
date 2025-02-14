<?php declare(strict_types=1);

namespace JsonPointer;

interface Document
{
	public function getId(): string;

	public function has(?string $path): bool;

	/**
	 * @return ($path is null ? array<string, mixed> : mixed)
	 */
	public function get(?string $path = null): mixed;

	public function findPathToParent(string $field, mixed $searchValue): string;

	/**
	 * @return array<string, string>
	 */
	public function findAllPaths(string $query): array;

	public function canResolveReference(Reference $ref): bool;

	public function resolveReference(Reference $ref): mixed;
}

<?php declare(strict_types=1);

namespace JsonPointer\ReferenceResolver;

use JsonPointer\Document;
use JsonPointer\Exceptions\DocumentParseError;
use JsonPointer\Reference;

final class ReferenceResolverCollection implements ReferenceResolver
{
	/** @var ReferenceResolver[] */
	private array $referenceResolvers;

	public function __construct(
		ReferenceResolver ... $referenceResolvers,
	) {
		$this->referenceResolvers = $referenceResolvers;
	}

	public function addResolver(ReferenceResolver $resolver): void
	{
		$this->referenceResolvers[] = $resolver;
	}

	public function supports(Reference $reference): bool
	{
		foreach ($this->referenceResolvers as $referenceResolver) {
			if ($referenceResolver->supports($reference)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function resolve(Reference $reference): Document
	{
		$e = null;
		foreach ($this->referenceResolvers as $referenceResolver) {
			if ($referenceResolver->supports($reference)) {
				try {
					return $referenceResolver->resolve($reference);
				}
				catch (\Throwable $e) {}
			}
		}

		throw DocumentParseError::fileNotFound($reference->getUri(), $e);
	}
}

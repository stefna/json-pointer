<?php declare(strict_types=1);

namespace JsonPointer\ReferenceResolver;

use JsonPointer\Document;
use JsonPointer\Exceptions\DocumentParseError;
use JsonPointer\Reference;

interface ReferenceResolver
{
	public function supports(Reference $reference): bool;

	/**
	 * @throws DocumentParseError
	 */
	public function resolve(Reference $reference): Document;
}

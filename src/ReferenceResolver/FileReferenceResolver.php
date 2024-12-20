<?php declare(strict_types=1);

namespace JsonPointer\ReferenceResolver;

use JsonPointer\Document;
use JsonPointer\DocumentFactory;
use JsonPointer\Reference;

final readonly class FileReferenceResolver implements ReferenceResolver
{
	private DocumentFactory $documentFactory;

	public function __construct(
		private string $root,
	) {
		$this->documentFactory = new DocumentFactory();
	}

	public function supports(Reference $reference): bool
	{
		return $reference->isExternal();
	}

	/**
	 * @inheritDoc
	 */
	public function resolve(Reference $reference): Document
	{
		$file = $reference->getUri();
		if (!file_exists($file)) {
			$file = $this->root . ltrim($file, '.');
		}
		$doc = $this->documentFactory->createFromFile($file);
		$reference->setRoot(dirname($file));
		return $doc;
	}
}

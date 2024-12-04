<?php declare(strict_types=1);

namespace JsonPointer;

use JsonPointer\Exceptions\DocumentParseError;

final readonly class DocumentFactory
{
	public function __construct(
		/** @deprecated use ReferenceResolver instead */
		private ?string $root = null,
	) {}

	/**
	 * @deprecated use ReferenceResolver instead
	 */
	public function findRoot(string|Reference $ref): ?string
	{
		if (!$this->root) {
			return null;
		}
		if ($ref instanceof Reference) {
			if (!$ref->isExternal()) {
				return null;
			}
			$ref = $ref->getUri();
		}

		$file = $this->root . ltrim($ref, '.');
		$info = pathinfo($file);

		if ($info['dirname'] . DIRECTORY_SEPARATOR === $this->root) {
			return null;
		}

		return substr($info['dirname'], strlen($this->root));
	}

	/**
	 * @deprecated use ReferenceResolver instead
	 */
	public function createFromReference(Reference $reference): Document&WritableDocument
	{
		if (!$reference->isExternal()) {
			throw new \InvalidArgumentException('Can only resolve external references files');
		}
		$file = ltrim($reference->getUri(), '.');
		if (!file_exists($file)) {
			throw DocumentParseError::fileNotFound($file);
		}
		return $this->createFromFile($file);
	}

	public function createFromFile(string $file): Document&WritableDocument
	{
		if (!file_exists($file)) {
			throw DocumentParseError::fileNotFound($file);
		}

		$content = file_get_contents($file);
		if (!$content) {
			throw DocumentParseError::invalidContent($file, 'File empty');
		}
		$errorMessage = null;
		if (str_ends_with($file, '.json')) {
			$doc = json_decode($content, true);
			$errorMessage = json_last_error_msg();
		}
		elseif (function_exists('yaml_parse')) {
			$doc = yaml_parse($content);
		}
		else {
			throw DocumentParseError::unknownFormat(substr($file, strrpos($file, '.') ?: 0));
		}
		if (!$doc || !is_array($doc) || array_is_list($doc)) {
			throw DocumentParseError::invalidContent(basename($file), $errorMessage);
		}
		// @phpstan-ignore argument.type
		return $this->createFromArray(basename($file), $doc);
	}

	/**
	 * @param array{"$id"?: string, id?: string, ...} $data
	 */
	public function createFromArray(string $id, array $data): Document&WritableDocument
	{
		if (isset($data['$id'])) {
			$id = rtrim($data['$id'], '/') . '/' . $id;
		}
		elseif (isset($data['id'])) {
			$id = rtrim($data['id'], '/') . '/' . $id;
		}

		return new BasicDocument($id, $data);
	}
}

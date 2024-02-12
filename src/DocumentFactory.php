<?php declare(strict_types=1);

namespace JsonPointer;

use JsonPointer\Exceptions\DocumentParseError;

final readonly class DocumentFactory
{
	public function __construct(
		private ?string $root = null,
	) {}

	public function createFromReference(Reference $reference): Document&WritableDocument
	{
		if (!$reference->isExternal()) {
			throw new \InvalidArgumentException('Can only resolve external references files');
		}
		$file = $this->root . ltrim($reference->getUri(), '.');
		if (!file_exists($file)) {
			throw DocumentParseError::fileNotFound($file);
		}
		return $this->createFromFile($file);
	}

	public function createFromFile(string $file): Document&WritableDocument
	{
		if ($this->root && !file_exists($file)) {
			$file = $this->root . ltrim($file, '.');
		}
		if (!file_exists($file)) {
			throw DocumentParseError::fileNotFound($file);
		}

		$content = file_get_contents($file);
		if (str_ends_with($file, '.json')) {
			$doc = json_decode($content, true);
		}
		elseif (function_exists('yaml_parse')) {
			$doc = yaml_parse($content);
		}
		else {
			throw DocumentParseError::unknownFormat(substr($file, strrpos($file, '.') ?: 0));
		}
		if (!$doc || !is_array($doc)) {
			throw DocumentParseError::invalidContent();
		}
		return $this->createFromArray(basename($file), $doc);
	}

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
}

<?php declare(strict_types=1);

namespace JsonPointer;

enum ReferenceType
{
	case Id;
	case Internal;
	case External;
	case ComplexExternal;
}

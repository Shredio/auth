<?php declare(strict_types = 1);

namespace Shredio\Auth\Metadata;

enum ParameterScope: string
{

	case User = 'user';
	case UserIdentity = 'user-identity';
	case Custom = 'custom';
	case Context = 'context';
	case RequirementChecker = 'requirement-checker';

}

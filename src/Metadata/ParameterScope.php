<?php declare(strict_types = 1);

namespace Shredio\Auth\Metadata;

enum ParameterScope: string
{

	case UserEntity = 'user-entity';
	case Custom = 'custom';
	case Context = 'context';
	case RequirementChecker = 'requirement-checker';

}

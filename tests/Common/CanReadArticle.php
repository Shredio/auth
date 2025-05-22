<?php declare(strict_types = 1);

namespace Tests\Common;

use Shredio\Auth\Requirement\SubjectRequirement;

final readonly class CanReadArticle implements SubjectRequirement
{

	public function __construct(
		public Article $article,
	)
	{
	}

}

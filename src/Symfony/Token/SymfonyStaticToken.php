<?php declare(strict_types = 1);

namespace Shredio\Auth\Symfony\Token;

use Shredio\Auth\Entity\UserEntity;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

final class SymfonyStaticToken extends AbstractToken
{

	public function __construct(UserEntity $entity)
	{
		parent::__construct($entity->getRoles());

		$this->setUser($entity);
	}

}

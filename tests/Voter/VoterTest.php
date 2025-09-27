<?php declare(strict_types = 1);

namespace Tests\Voter;

use PHPUnit\Framework\TestCase;
use Shredio\Auth\Attribute\VoteMethod;
use Shredio\Auth\Context\VoterContext;
use Shredio\Auth\Exception\ForbiddenException;
use Shredio\Auth\Exception\InvalidVoterException;
use Shredio\Auth\Entity\UserEntity;
use Shredio\Auth\Resolver\VoterParameterResolver;
use Shredio\Auth\Symfony\Adapter\VoterAdapter;
use Shredio\Auth\Symfony\SymfonyRoleVoter;
use Shredio\Auth\Symfony\SymfonyUserRequirementChecker;
use Shredio\Auth\UserRequirementChecker;
use Shredio\Auth\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Tests\Common\Article;
use Tests\Common\CanCreateArticle;
use Tests\Common\CanReadArticle;
use Tests\Common\HasAdminRole;
use Tests\Common\HasEditorRule;
use Tests\Common\HasUserRole;
use Tests\Common\LazyVoterIterator;
use Tests\Common\MyVoterService;
use Tests\Common\User;

final class VoterTest extends TestCase
{

	private LazyVoterIterator $voters;

	private SymfonyUserRequirementChecker $requirementChecker;

	protected function setUp(): void
	{
		$roleHierarchyVoter = new RoleHierarchyVoter(new RoleHierarchy([
			'ROLE_EDITOR' => ['ROLE_USER'],
			'ROLE_ADMIN' => ['ROLE_EDITOR'],
		]));

		$this->voters = new LazyVoterIterator([$roleHierarchyVoter]);
		$accessDecisionManager = new AccessDecisionManager($this->voters);
		$this->voters->append[] = new SymfonyRoleVoter($accessDecisionManager);
		$this->requirementChecker = new SymfonyUserRequirementChecker($accessDecisionManager);
	}

	public function testNullableUser(): void
	{
		$this->voters->list[] = $this->addVoter(new class implements Voter {

			#[VoteMethod]
			public function voteOnCreate(CanCreateArticle $requirement, ?User $user = null): bool
			{
				return (bool) $user;
			}

		});

		$this->assertFalse($this->requirementChecker->isSatisfied(null, new CanCreateArticle()));
		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity(), new CanCreateArticle()));
	}

	public function testProtectedMethod(): void
	{
		$this->voters->list[] = $this->addVoter(new class implements Voter {

			#[VoteMethod]
			protected function voteOnCreate(CanCreateArticle $requirement, ?User $user = null): bool
			{
				return (bool) $user;
			}

		});

		$this->expectException(InvalidVoterException::class);

		$this->requirementChecker->isSatisfied($this->createIdentity(), new CanCreateArticle());
	}

	public function testPrivateMethod(): void
	{
		$this->voters->list[] = $this->addVoter(new class implements Voter {

			#[VoteMethod]
			private function voteOnCreate(CanCreateArticle $requirement, ?User $user = null): bool
			{
				return (bool) $user;
			}

		});

		$this->expectException(InvalidVoterException::class);

		$this->requirementChecker->isSatisfied($this->createIdentity(), new CanCreateArticle());
	}

	public function testRequiredUser(): void
	{
		$this->voters->list[] = $this->addVoter(new class implements Voter {

			#[VoteMethod]
			public function voteOnCreate(CanCreateArticle $requirement, User $user): bool
			{
				return true;
			}

		});

		$this->assertFalse($this->requirementChecker->isSatisfied(null, new CanCreateArticle()));
		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity(), new CanCreateArticle()));
	}

	public function testSubject(): void
	{
		$this->voters->list[] = $this->addVoter(new class implements Voter {

			#[VoteMethod]
			public function voteOnRead(CanReadArticle $requirement, User $user): bool
			{
				return $requirement->article->id === 5;
			}

		});

		$this->assertFalse($this->requirementChecker->isSatisfied(null, new CanReadArticle(new Article(5))));
		$this->assertFalse($this->requirementChecker->isSatisfied($this->createIdentity(), new CanReadArticle(new Article(1))));
		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity(), new CanReadArticle(new Article(5))));
	}

	public function testRequirementUnion(): void
	{
		$this->voters->list[] = $this->addVoter(new class implements Voter {

			#[VoteMethod]
			public function voteOn(CanReadArticle|CanCreateArticle $requirement, ?User $user): bool
			{
				if ($requirement instanceof CanReadArticle) {
					return $user && $requirement->article->id === 5;
				}

				return (bool) $user;
			}

		});

		$this->assertFalse($this->requirementChecker->isSatisfied(null, new CanReadArticle(new Article(5))));
		$this->assertFalse($this->requirementChecker->isSatisfied($this->createIdentity(), new CanReadArticle(new Article(1))));
		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity(), new CanReadArticle(new Article(5))));

		$this->assertFalse($this->requirementChecker->isSatisfied(null, new CanCreateArticle()));
		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity(), new CanCreateArticle()));
	}

	public function testContext(): void
	{
		$this->voters->list[] = $this->addVoter(new class implements Voter {

			#[VoteMethod]
			public function voteOnCreate(CanCreateArticle $requirement, VoterContext $context): bool
			{
				return $context->isCurrentUserLoggedIn();
			}

		});

		$this->assertFalse($this->requirementChecker->isSatisfied(null, new CanCreateArticle()));
		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity(), new CanCreateArticle()));
	}

	public function testService(): void
	{
		$this->voters->list[] = $this->addVoter(new class implements Voter {

			#[VoteMethod]
			public function voteOnCreate(CanCreateArticle $requirement, MyVoterService $service): bool
			{
				return $service->isLoggedIn();
			}

		});

		$this->assertFalse($this->requirementChecker->isSatisfied(null, new CanCreateArticle()));
		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity(), new CanCreateArticle()));
	}

	public function testRequirementChecker(): void
	{
		$this->addBasicVoters();

		$this->assertFalse($this->requirementChecker->isSatisfied(null, new CanCreateArticle()));
		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity(), new CanCreateArticle()));
	}

	public function testRequireThrowsForbiddenException(): void
	{
		$this->addBasicVoters();

		$this->expectException(ForbiddenException::class);
		$this->expectExceptionMessage('Access Denied. The user does not have access to "CanCreateArticle" (requires logged in user).');

		$this->requirementChecker->require(null, new CanCreateArticle());
	}

	public function testRequireDoesNotThrowForbiddenException(): void
	{
		$this->addBasicVoters();

		$this->requirementChecker->require($this->createIdentity(), new CanCreateArticle());
		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity(), new CanCreateArticle()));
	}

	public function testRolesForGuest(): void
	{
		$role = 'ROLE_GUEST';

		$this->assertFalse($this->requirementChecker->isSatisfied($this->createIdentity($role), new HasUserRole()));
		$this->assertFalse($this->requirementChecker->isSatisfied($this->createIdentity($role), new HasEditorRule()));
		$this->assertFalse($this->requirementChecker->isSatisfied($this->createIdentity($role), new HasAdminRole()));
	}

	public function testRolesForUser(): void
	{
		$role = 'ROLE_USER';

		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity($role), new HasUserRole()));
		$this->assertFalse($this->requirementChecker->isSatisfied($this->createIdentity($role), new HasEditorRule()));
		$this->assertFalse($this->requirementChecker->isSatisfied($this->createIdentity($role), new HasAdminRole()));
	}

	public function testRolesForEditor(): void
	{
		$role = 'ROLE_EDITOR';

		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity($role), new HasUserRole()));
		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity($role), new HasEditorRule()));
		$this->assertFalse($this->requirementChecker->isSatisfied($this->createIdentity($role), new HasAdminRole()));
	}

	public function testRolesForAdmin(): void
	{
		$role = 'ROLE_ADMIN';

		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity($role), new HasUserRole()));
		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity($role), new HasEditorRule()));
		$this->assertTrue($this->requirementChecker->isSatisfied($this->createIdentity($role), new HasAdminRole()));
	}

	private function createIdentity(string $role = 'ROLE_USER'): UserEntity
	{
		return new User(1, $role);
	}

	private function addVoter(Voter $voter): VoterAdapter
	{
		return new VoterAdapter($voter, new VoterParameterResolver($this->requirementChecker));
	}

	private function addBasicVoters(): void
	{
		$this->voters->list[] = $this->addVoter(new class implements Voter {

			#[VoteMethod]
			public function voteOnRead(CanReadArticle $requirement, User $user): bool
			{
				return $requirement->article->id === 5;
			}

		});
		$this->voters->list[] = $this->addVoter(new class implements Voter {

			#[VoteMethod]
			public function voteOnCreate(CanCreateArticle $requirement, UserRequirementChecker $userRequirementChecker, UserEntity $identity): bool
			{
				return $userRequirementChecker->isSatisfied($identity, new CanReadArticle(new Article(5)));
			}

		});
	}

}

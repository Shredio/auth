<?php declare(strict_types = 1);

namespace Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Shredio\Auth\Attribute\VoteMethod;
use Shredio\Auth\Context\VoterContext;
use Shredio\Auth\Exception\InvalidVoterException;
use Shredio\Auth\Exception\InvalidVoterParameterException;
use Shredio\Auth\Metadata\VoterMetadataFactory;
use stdClass;
use Tests\Common\CanCreateArticle;
use Tests\Common\CanReadArticle;
use Tests\Common\FooService;
use Tests\Common\User;

final class VoterMetadataFactoryTest extends TestCase
{

	public function testMissingRequirement(): void
	{
		$factory = new VoterMetadataFactory();
		$voter = new class {

			#[VoteMethod]
			public function voteOnCreate(): bool
			{
				return true;
			}

		};

		$this->expectException(InvalidVoterException::class);

		$factory->create($voter::class);
	}

	public function testSubject(): void
	{
		$factory = new VoterMetadataFactory();
		$voter = new class {

			#[VoteMethod]
			public function voteOnRead(CanReadArticle $requirement): bool
			{
				return true;
			}

		};

		$meta = $factory->create($voter::class);

		$this->assertSame([], $meta->getParameterSchema(CanReadArticle::class, 'voteOnRead'));
	}

	public function testUser(): void
	{
		$factory = new VoterMetadataFactory();
		$voter = new class {

			#[VoteMethod]
			public function voteOnCreate(CanCreateArticle $requirement, User $user): bool
			{
				return true;
			}

		};

		$meta = $factory->create($voter::class);
		$this->assertSame('voteOnCreate', $meta->getMethodName(CanCreateArticle::class));
		$this->assertSame([
			['scope' => 'user', 'classType' => null, 'nullable' => false],
		], $meta->getParameterSchema(CanCreateArticle::class, 'voteOnCreate'));
	}

	public function testNullableUser(): void
	{
		$factory = new VoterMetadataFactory();
		$voter = new class {

			#[VoteMethod]
			public function voteOnCreate(CanCreateArticle $requirement, ?User $user): bool
			{
				return true;
			}

		};

		$meta = $factory->create($voter::class);
		$this->assertSame([
			['scope' => 'user', 'classType' => null, 'nullable' => true],
		], $meta->getParameterSchema(CanCreateArticle::class, 'voteOnCreate'));
	}

	public function testExtraArgument(): void
	{
		$factory = new VoterMetadataFactory();
		$voter = new class {

			#[VoteMethod]
			public function voteOnCreate(CanCreateArticle $requirement, stdClass $payload): bool
			{
				return true;
			}

		};

		$this->expectException(InvalidVoterParameterException::class);

		$factory->create($voter::class);
	}

	public function testCustomService(): void
	{
		$factory = new VoterMetadataFactory();
		$voter = new class {

			#[VoteMethod]
			public function voteOnCreate(CanCreateArticle $requirement, FooService $service): bool
			{
				return true;
			}

		};

		$meta = $factory->create($voter::class);

		$this->assertSame([
			['scope' => 'custom', 'classType' => FooService::class, 'nullable' => false],
		], $meta->getParameterSchema(CanCreateArticle::class, 'voteOnCreate'));
	}

	public function testVoterContext(): void
	{
		$factory = new VoterMetadataFactory();
		$voter = new class {

			#[VoteMethod]
			public function voteOnCreate(CanCreateArticle $requirement, VoterContext $context): bool
			{
				return true;
			}

		};

		$meta = $factory->create($voter::class);

		$this->assertSame([
			['scope' => 'context', 'classType' => null, 'nullable' => false],
		], $meta->getParameterSchema(CanCreateArticle::class, 'voteOnCreate'));
	}

	public function testNullableVoterContext(): void
	{
		$factory = new VoterMetadataFactory();
		$voter = new class {

			#[VoteMethod]
			public function voteOnCreate(CanCreateArticle $requirement, ?VoterContext $context): bool
			{
				return true;
			}

		};

		$this->expectException(InvalidVoterParameterException::class);

		$factory->create($voter::class);
	}

	public function testMultipleRequirements(): void
	{
		$factory = new VoterMetadataFactory();
		$voter = new class {

			#[VoteMethod]
			public function voteOnCreate(CanCreateArticle|CanReadArticle $requirement): bool
			{
				return true;
			}

		};

		$meta = $factory->create($voter::class);

		$this->assertSame('voteOnCreate', $meta->getMethodName(CanCreateArticle::class));
		$this->assertSame('voteOnCreate', $meta->getMethodName(CanReadArticle::class));

		$this->assertSame([], $meta->getParameterSchema(CanCreateArticle::class, 'voteOnCreate'));
		$this->assertSame([], $meta->getParameterSchema(CanReadArticle::class, 'voteOnCreate'));
	}

}

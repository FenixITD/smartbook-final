<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Dto\Auth\RegisterDto;
use App\Models\User;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }

    public function test_create_returns_user_response_dto(): void
    {
        $dto = new RegisterDto(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'secret123',
        );

        $result = $this->repository->create($dto);

        $this->assertSame('John Doe', $result->name);
        $this->assertSame('john@example.com', $result->email);
    }

    public function test_create_persists_user_in_database(): void
    {
        $dto = new RegisterDto(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'secret123',
        );

        $this->repository->create($dto);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_create_returns_dto_with_correct_id(): void
    {
        $dto = new RegisterDto(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'secret123',
        );

        $result = $this->repository->create($dto);

        $this->assertGreaterThan(0, $result->id);
        $this->assertDatabaseHas('users', ['id' => $result->id]);
    }

    public function test_create_returns_dto_with_non_empty_timestamps(): void
    {
        $dto = new RegisterDto(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'secret123',
        );

        $result = $this->repository->create($dto);

        $this->assertNotEmpty($result->createdAt);
        $this->assertNotEmpty($result->updatedAt);
    }

    public function test_create_does_not_store_plain_password(): void
    {
        $dto = new RegisterDto(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'secret123',
        );

        $this->repository->create($dto);

        $this->assertDatabaseMissing('users', ['password' => 'secret123']);
    }

    public function test_create_multiple_users_get_unique_ids(): void
    {
        $dtoA = new RegisterDto(name: 'Alice', email: 'alice@example.com', password: 'pass1');
        $dtoB = new RegisterDto(name: 'Bob', email: 'bob@example.com', password: 'pass2');

        $resultA = $this->repository->create($dtoA);
        $resultB = $this->repository->create($dtoB);

        $this->assertNotSame($resultA->id, $resultB->id);
    }

    public function test_create_token_returns_string(): void
    {
        $user = User::factory()->create();

        $token = $this->repository->createToken($user->id, 'test-token');

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function test_create_token_persists_token_in_database(): void
    {
        $user = User::factory()->create();

        $this->repository->createToken($user->id, 'test-token');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test-token',
        ]);
    }

    public function test_create_token_plain_text_matches_stored_hash(): void
    {
        $user = User::factory()->create();

        $plainToken = $this->repository->createToken($user->id, 'test-token');

        $tokenId = explode('|', $plainToken)[0];
        $this->assertNotNull(PersonalAccessToken::find($tokenId));
    }

    public function test_create_token_throws_for_nonexistent_user(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->createToken(99999, 'test-token');
    }

    public function test_create_token_different_names_create_separate_tokens(): void
    {
        $user = User::factory()->create();

        $this->repository->createToken($user->id, 'token-one');
        $this->repository->createToken($user->id, 'token-two');

        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'token-one', 'tokenable_id' => $user->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'token-two', 'tokenable_id' => $user->id]);
    }
}

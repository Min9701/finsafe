<?php

namespace Tests\Unit\Services;

use App\Models\Transaction;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use App\Services\TransactionService;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    private TransactionRepositoryInterface&MockInterface $transactionRepo;

    private CategoryRepositoryInterface&MockInterface $categoryRepo;

    private TransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionRepo = Mockery::mock(TransactionRepositoryInterface::class);
        $this->categoryRepo = Mockery::mock(CategoryRepositoryInterface::class);
        $this->service = new TransactionService($this->transactionRepo, $this->categoryRepo);
    }

    public function test_create_transaction_injects_user_id(): void
    {
        $userId = 1;
        $data = ['amount' => 1000, 'type' => 'income'];
        $expected = new Transaction(array_merge($data, ['user_id' => $userId]));

        $this->transactionRepo->shouldReceive('create')
            ->once()
            ->with(Mockery::subset(['user_id' => $userId, 'amount' => 1000]))
            ->andReturn($expected);

        $result = $this->service->createTransaction($userId, $data);

        $this->assertInstanceOf(Transaction::class, $result);
    }

    public function test_authorize_transaction_throws_exception_for_other_user(): void
    {
        $transaction = new Transaction;
        $transaction->user_id = 999;

        $this->expectException(AccessDeniedHttpException::class);
        $this->service->authorizeTransaction(1, $transaction);
    }

    public function test_authorize_transaction_passes_for_owner(): void
    {
        $transaction = new Transaction;
        $transaction->user_id = 1;

        $this->service->authorizeTransaction(1, $transaction);
        $this->assertSame(1, $transaction->user_id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

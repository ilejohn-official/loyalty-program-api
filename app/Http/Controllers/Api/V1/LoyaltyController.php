<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessPurchaseEvent;
use App\Services\LoyaltyService;
use App\Http\Requests\Api\V1\MonthlySummaryRequest;
use App\Http\Requests\Api\V1\ProcessPurchaseRequest;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
  public function __construct(
    protected LoyaltyService $loyaltyService
  ) {}

  /**
   * Get user's loyalty points and transactions.
   *
   * @param  \App\Models\User  $user
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(User $user): JsonResponse
  {
    return response()->json([
      'data' => $this->loyaltyService->getUserStats($user)
    ]);
  }

  /**
   * Process a purchase event.
   *
   * @param  ProcessPurchaseRequest  $request
   * @param  User  $user
   * @return \Illuminate\Http\JsonResponse
   */
  public function processPurchase(ProcessPurchaseRequest $request, User $user): JsonResponse
  {
    ProcessPurchaseEvent::dispatch(
      $user,
      $request->validated('amount'),
      $request->validated('transaction_reference')
    );

    return response()->json([
      'message' => 'Purchase event queued for processing',
      'status' => 'pending'
    ]);
  }

  /**
   * Get monthly loyalty summary.
   *
   * @param  MonthlySummaryRequest  $request
   * @param  User  $user
   * @param  string  $yearMonth
   * @return \Illuminate\Http\JsonResponse
   */
  public function monthlySummary(MonthlySummaryRequest $request, User $user, string $yearMonth): JsonResponse
  {
    return response()->json([
      'data' => $this->loyaltyService->getMonthlySummary($user, $yearMonth)
    ]);
  }
}

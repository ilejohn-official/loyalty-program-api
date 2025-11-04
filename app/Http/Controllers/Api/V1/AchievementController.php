<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ShowAchievementRequest;
use App\Http\Resources\AchievementResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Http\JsonResponse;

class AchievementController extends Controller
{
  public function __construct(
    protected AchievementService $achievementService
  ) {}

  /**
   * Get user's achievements and progress.
   *
   * @param  User  $user
   * @return JsonResponse
   */
  public function index(User $user): JsonResponse
  {
    $achievementData = $this->achievementService->getUserProgress($user);

    return response()->json([
      'data' => array_merge(
        $achievementData,
        ['user' => new UserResource($user)]
      )
    ]);
  }

  /**
   * Get specific achievement details.
   *
   * @param  ShowAchievementRequest  $request
   * @param  User  $user
   * @param  string  $achievementType
   * @return JsonResponse
   */
  public function show(ShowAchievementRequest $request, User $user, string $achievementType): JsonResponse
  {
    return response()->json([
      'data' => new AchievementResource(
        $this->achievementService->getUserAchievementByType($user, $achievementType)
      )
    ]);
  }
}

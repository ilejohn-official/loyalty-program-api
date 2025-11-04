<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\BadgeResource;
use App\Http\Requests\Api\V1\ShowBadgeRequest;

class BadgeController extends Controller
{
  public function __construct(
    protected BadgeService $badgeService
  ) {}

  /**
   * Get user's badges.
   *
   * @param  \App\Models\User  $user
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(User $user): JsonResponse
  {
    $badgeData = $this->badgeService->getUserBadges($user);

    return response()->json([
      'data' => array_merge(
        $badgeData,
        ['user' => new UserResource($user)]
      )
    ]);
  }

  /**
   * Get specific badge details.
   *
   * @param  ShowBadgeRequest  $request
   * @param  User  $user
   * @param  string  $badgeType
   * @return \Illuminate\Http\JsonResponse
   */
  public function show(ShowBadgeRequest $request, User $user, string $badgeType): JsonResponse
  {
    return response()->json([
      'data' => new BadgeResource(
        $this->badgeService->getUserBadgeByType($user, $badgeType)
      )
    ]);
  }
}

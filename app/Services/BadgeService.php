<?php

namespace App\Services;

use App\DTOs\UserDto;
use App\Models\Badge;
use App\Enums\BadgeType;
use App\Models\Achievement;
use App\Events\BadgeUnlocked;
use Illuminate\Support\Facades\DB;


class BadgeService
{
  public function checkAndUnlockBadges(UserDto $user): void
  {
    // Count completed achievements (unlocked records)
    $completedAchievementsCount = Achievement::query()
      ->where('user_id', $user->id)
      ->count();

    // Badge thresholds from config
    $thresholds = config('loyalty.badges.thresholds', [
      BadgeType::BRONZE_SPENDER->value => 5,
      BadgeType::SILVER_SPENDER->value => 10,
      BadgeType::GOLD_SPENDER->value => 25,
      BadgeType::LOYAL_CUSTOMER->value => 15,
      BadgeType::VIP_MEMBER->value => 50,
    ]);

    foreach ($thresholds as $badgeTypeValue => $required) {
      if ($completedAchievementsCount >= $required) {
        $badgeType = BadgeType::from($badgeTypeValue);
        // If user doesn't already have this badge, award it
        $exists = Badge::query()
          ->where('user_id', $user->id)
          ->where('badge_type', $badgeType)
          ->exists();

        if (! $exists) {
          $badge = Badge::create([
            'user_id' => $user->id,
            'badge_type' => $badgeType,
            'level' => $badgeType->getDefaultLevel(),
            'earned_at' => now(),
          ]);
          event(new BadgeUnlocked($user, $badge));
        }
      }
    }
  }

  public function getUserBadges(UserDto $user): array
  {
    $badgeStats = Badge::query()
      ->where('user_id', $user->id)
      ->selectRaw('
        COUNT(*) as total_earned,
        MAX(level) as highest_level,
        JSON_ARRAYAGG(
          JSON_OBJECT(
            "id", id,
            "badge_type", badge_type,
            "level", level,
            "earned_at", earned_at
          )
        ) as badge_details
      ')
      ->first();

    $badges = json_decode($badgeStats->badge_details ?? '[]', true);

    return [
      'badges' => $badges,
      'total_earned' => $badgeStats->total_earned ?? 0,
      'highest_level' => $badgeStats->highest_level,
    ];
  }

  public function getUserBadgeByType(UserDto $user, BadgeType $badgeType): Badge
  {
    return Badge::query()
      ->where('user_id', $user->id)
      ->where('badge_type', $badgeType)
      ->firstOrFail();
  }
}

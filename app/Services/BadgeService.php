<?php

namespace App\Services;

use App\Events\BadgeUnlocked;
use App\Models\Badge;
use App\Models\Achievement;
use App\DTOs\UserDto;
use Illuminate\Support\Facades\DB;


class BadgeService
{
  public function checkAndUnlockBadges(UserDto $user): void
  {
    // Count completed achievements (unlocked records)
    $completedAchievementsCount = Achievement::query()
      ->where('user_id', $user->id)
      ->count();

    // Simple badge thresholds can be configured in config/loyalty.php
    $thresholds = config('loyalty.badges.thresholds', [
      'first' => 1,
      'bronze' => 5,
      'silver' => 10,
      'gold' => 25,
    ]);

    foreach ($thresholds as $badgeType => $required) {
      if ($completedAchievementsCount >= $required) {
        // If user doesn't already have this badge, award it
        $exists = Badge::query()
          ->where('user_id', $user->id)
          ->where('badge_type', $badgeType)
          ->exists();

        if (! $exists) {
          $badge = Badge::create([
            'user_id' => $user->id,
            'badge_type' => $badgeType,
            'level' => 1,
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

  public function getUserBadgeByType(UserDto $user, string $badgeType): Badge
  {
    return Badge::query()
      ->where('user_id', $user->id)
      ->where('badge_type', $badgeType)
      ->firstOrFail();
  }
}

<?php

namespace App\Services;

use App\Events\BadgeUnlocked;
use App\Models\Badge;
use App\Models\User;

class BadgeService
{
  public function checkAndUnlockBadges(User $user): void
  {
    $completedAchievementsCount = $user->achievementProgress()
      ->where('completed', true)
      ->count();

    $eligibleBadges = Badge::where('required_achievements', '<=', $completedAchievementsCount)
      ->whereNotIn('id', function ($query) use ($user) {
        $query->select('badge_id')
          ->from('user_badges')
          ->where('user_id', $user->id);
      })
      ->get();

    foreach ($eligibleBadges as $badge) {
      $user->badges()->attach($badge->id, ['unlocked_at' => now()]);
      event(new BadgeUnlocked($user, $badge));
    }
  }

  public function getUserBadges(User $user): array
  {
    $badges = Badge::query()
      ->forUser($user->id)
      ->with(['user'])
      ->get();

    return [
      'badges' => $badges->map(function ($badge) {
        return [
          'id' => $badge->id,
          'name' => $badge->name,
          'description' => $badge->description,
          'image_url' => $badge->image_url,
          'unlocked_at' => $badge->pivot->unlocked_at
        ];
      }),
      'total_earned' => $badges->count(),
      'highest_level' => $badges->max('level')
    ];
  }

  public function getUserBadgeByType(User $user, string $badgeType): Badge
  {
    return Badge::query()
      ->forUser($user->id)
      ->where('badge_type', $badgeType)
      ->firstOrFail();
  }
}

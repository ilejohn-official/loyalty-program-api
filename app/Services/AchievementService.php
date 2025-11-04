<?php

namespace App\Services;

use App\Events\AchievementUnlocked;
use App\Models\Achievement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AchievementService
{
  public function checkAndUnlockAchievements(User $user, float $amount): void
  {
    $eligibleAchievements = Achievement::whereNotIn('id', function ($query) use ($user) {
      $query->select('achievement_id')
        ->from('achievement_progress')
        ->where('user_id', $user->id)
        ->where('completed', true);
    })->get();

    foreach ($eligibleAchievements as $achievement) {
      $progress = $user->achievementProgress()
        ->firstOrCreate(['achievement_id' => $achievement->id]);

      if ($achievement->type === 'purchase_amount') {
        $progress->progress += $amount;
      } elseif ($achievement->type === 'purchase_count') {
        $progress->progress += 1;
      }

      if ($progress->progress >= $achievement->target && !$progress->completed) {
        $progress->completed = true;
        $progress->completed_at = now();
        $progress->save();

        event(new AchievementUnlocked($user, $achievement));
      } else {
        $progress->save();
      }
    }
  }

  public function getUserProgress(User $user): array
  {
    $achievements = Achievement::query()
      ->forUser($user->id)
      ->with(['user'])
      ->get();

    return [
      'achievements' => $achievements->map(function ($achievement) {
        return [
          'id' => $achievement->id,
          'name' => $achievement->name,
          'description' => $achievement->description,
          'progress' => $achievement->progress ?? 0,
          'target' => $achievement->target,
          'completed' => (bool) ($achievement->completed ?? false),
          'completed_at' => $achievement->completed_at
        ];
      }),
      'total_unlocked' => $achievements->where('completed', true)->count()
    ];
  }

  public function getUserAchievementByType(User $user, string $achievementType): Achievement
  {
    return Achievement::query()
      ->forUser($user->id)
      ->where('achievement_type', $achievementType)
      ->firstOrFail();
  }
}

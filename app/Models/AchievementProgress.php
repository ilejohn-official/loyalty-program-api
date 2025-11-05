<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $user_id
 * @property string $achievement_type
 * @property float $current_value
 * @property float $target_value
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AchievementProgress extends Model
{
  use HasFactory;

  protected $table = 'achievement_progress';

  protected $fillable = [
    'user_id',
    'achievement_type',
    'current_value',
    'target_value'
  ];

  protected $casts = [
    'current_value' => 'float',
    'target_value' => 'float',
  ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class LoyaltyTransaction
 *
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property string $type
 * @property int $points_earned
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class LoyaltyTransaction extends Model
{
  use HasFactory;

  public $timestamps = false;

  protected $fillable = [
    'user_id',
    'amount',
    'type',
    'points_earned',
    'created_at',
  ];

  protected $casts = [
    'amount' => 'decimal:2',
    'points_earned' => 'integer',
    'created_at' => 'datetime',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Scope transactions by month (YYYYMM) using created_at.
   */
  public function scopeForMonth(Builder $query, string $yyyymm): Builder
  {
    return $query->whereRaw("DATE_FORMAT(created_at, '%Y%m') = ?", [$yyyymm]);
  }
}

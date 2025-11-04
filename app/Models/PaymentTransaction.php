<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class PaymentTransaction
 *
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property string|null $provider_reference
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PaymentTransaction extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'user_id',
    'amount',
    'provider_reference',
    'status',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'amount' => 'decimal:2',
  ];

  /**
   * The user who made the payment.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}

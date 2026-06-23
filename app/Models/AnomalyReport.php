<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int    $id
 * @property string $numero_tg
 * @property string $field_reported
 * @property string $description
 * @property string $status
 */
class AnomalyReport extends Model
{
    protected $fillable = [
        'vehicle_id', 'numero_tg', 'field_reported', 'description',
        'reporter_user_id', 'reporter_email', 'status', 'admin_note', 'ip_address',
    ];

    public function vehicle(): BelongsTo  { return $this->belongsTo(Vehicle::class); }
    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reporter_user_id'); }
}

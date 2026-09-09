<?php

namespace App\Models;

use App\Enums\WhatsAppDirection;
use Database\Factories\WhatsAppMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    /** @use HasFactory<WhatsAppMessageFactory> */
    use HasFactory;

    protected $table = 'whatsapp_messages';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'direction',
        'wa_message_id',
        'payload',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'direction' => WhatsAppDirection::class,
            'payload' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

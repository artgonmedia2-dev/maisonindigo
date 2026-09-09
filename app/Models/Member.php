<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Membre de la maison (newsletter, ventes privées), synchronisé avec MailerLite.
 */
class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'phone',
        'source',
        'mailerlite_id',
        'consent_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'consent_at' => 'datetime',
        ];
    }

    public function hasConsented(): bool
    {
        return $this->consent_at !== null;
    }
}

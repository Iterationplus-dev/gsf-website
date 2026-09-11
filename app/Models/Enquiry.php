<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory;

    protected $table = 'enquiries';

    protected $guarded = ['id'];

    public const TYPES = [
        'contact' => 'Contact',
        'partnership' => 'Partnership',
        'volunteer' => 'Volunteer',
    ];

    public const STATUSES = [
        'new' => 'New',
        'in_progress' => 'In progress',
        'closed' => 'Closed',
    ];

    protected function casts(): array
    {
        return ['consented_at' => 'datetime'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'due_date',
        'priority',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
    ];

    /**
     * Valid one-way status transitions.
     * A task can only move forward, never skip or revert.
     */
    public static array $statusFlow = [
        'pending'     => 'in_progress',
        'in_progress' => 'done',
    ];

    /**
     * Returns the next allowed status, or null if already done.
     */
    public function nextStatus(): ?string
    {
        return self::$statusFlow[$this->status] ?? null;
    }
}
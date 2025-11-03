<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'html_content',
        'updated_by',
    ];

    // Disable automatic timestamp management
    public $timestamps = false;

    /**
     * Relacionamento com usuário que fez a atualização
     */
    public function updatedByUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope para pegar a versão mais recente
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('updated_at', 'desc');
    }
    /**
     * Retorna o tutorial atual(mais recente)
     */
    public static function getCurrentTutorial()
    {
        return self::orderBy('updated_at', 'desc')->first();
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tutorial) {
            if(empty($tutorial->content)){
                $tutorial->content = '';
            }

            if(empty($tutorial->html_content)){
                $tutorial->html_content = '';
            }

            // define usuário atual se não especificado
            if (empty($tutorial->updated_by) && auth()->check()) {
                $tutorial->updated_by = auth()->id();
            }
        });
    }



}

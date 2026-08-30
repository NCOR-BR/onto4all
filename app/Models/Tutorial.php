<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tutorial extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'html_content',
        'updated_by',
        'locale',
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
    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public static function getCurrentTutorial(?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return self::forLocale($locale)
            ->orderBy('updated_at', 'desc')
            ->first();
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

            if (empty($tutorial->locale)) {
                $tutorial->locale = app()->getLocale();
            }
        });
    }

    public function getBoxedHtmlAttribute(): string
    {
        $html = $this->html_content;

        if (empty($html)) {
            return '';
        }

        if (Str::contains($html, 'class="box') || Str::contains($html, "class='box")) {
            return $html;
        }

        $breakMarker = '<!--TUTORIAL_SECTION_BREAK-->';

        $blankParagraphPattern = '(?:<p[^>]*>(?:\s|&nbsp;|<br\s*\/?>\s*)*<\/p>\s*)';

        $normalized = preg_replace(
            "/{$blankParagraphPattern}{3,}|(?:\r?\n){3,}/i",
            $breakMarker,
            $html
        );

        if (!Str::contains($normalized, $breakMarker)) {
            return '<div class="box box-solid"><div class="box-body">' . $html . '</div></div>';
        }

        $rawSections = explode($breakMarker, $normalized);

        $sections = array_filter(array_map(function ($section) {
            $section = trim($section);

            // Remove quebras vazias no início/fim do bloco
            $section = preg_replace('/^(?:<p[^>]*>(?:\s|&nbsp;|<br\s*\/?\s*>)*<\/p>\s*)+/i', '', $section);
            $section = preg_replace('/(?:<p[^>]*>(?:\s|&nbsp;|<br\s*\/?\s*>)*<\/p>\s*)+$/i', '', $section);

            if ($section === '') {
                return null;
            }

            $plainText = trim(strip_tags($section));
            $hasMedia = preg_match('/<(img|video|iframe|object|svg|table|ul|ol|li)\b/i', $section);

            if ($plainText === '' && !$hasMedia) {
                return null;
            }

            return $section;
        }, $rawSections));

        if (empty($sections)) {
            return '<div class="box box-solid"><div class="box-body">' . $html . '</div></div>';
        }

        return implode("\n", array_map(function ($section) {
            return '<div class="box box-solid"><div class="box-body">' . $section . '</div></div>';
        }, $sections));
    }

}

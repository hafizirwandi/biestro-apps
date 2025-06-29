<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Survey extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'survey';
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];
    protected $fillable = [
        'title',
        'description',
        'start_at',
        'end_at',
        'slug_link',
        'status'
    ];
    public function question()
    {
        return $this->hasMany(Question::class, 'survey_id', 'id')->orderBy('position', 'asc');
    }
    protected static function booted()
    {
        static::creating(function ($survey) {
            if (empty($survey->slug_link)) {
                $survey->slug_link = static::generateUniqueSlug($survey->title);
            }
        });
    }

    public static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $count = static::where('slug_link', 'like', "{$slug}%")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }
    public function responses()
    {
        return $this->hasMany(Response::class, 'survey_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('survey')
            ->logFillable();
    }
}

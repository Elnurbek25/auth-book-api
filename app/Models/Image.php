<?php

namespace App\Models;


use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'imageable_id',
        'imageable_type',
        'path',
       // 'url',
    ];

    public function user()

    {
        return $this->belongsTo(User::class);
    }
    public function imageable()
    {
        return $this->morohTo();
    }
    public function url(): Attribute
    {
        return Attribute::make(fn(): string => URL::to('app/public/' . $this->path));
    }
    
}

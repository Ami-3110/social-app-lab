<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PostMedia extends Model
{
  protected $table = 'post_media';

  protected $fillable = [
    'post_id',
    'path',
    'sort_order',
  ];

  protected $appends = [
    'url',
  ];

  public function post()
  {
    return $this->belongsTo(Post::class);
  }

  public function getUrlAttribute(): ?string
  {
    return $this->path
      ? Storage::disk('public')->url($this->path) //error ignore
      : null;
  }
}

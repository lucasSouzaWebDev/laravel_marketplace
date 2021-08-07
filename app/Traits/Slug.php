<?php

namespace App\Traits;

use Illuminate\Support\Str;


trait Slug
{
    public function setNameAttribute($slug)
    {
        $slug = Str::slug($slug);
        $matchs = $this->uniqueSlug($slug);
        $this->attributes['name'] = $slug;
        $this->attributes['slug'] = $matchs ? $slug . '-' . $matchs : $slug;
    }

    public function uniqueSlug($slug)
    {
        return $this->whereRaw("slug REGEXP '^{$slug}(-0-9*)?$'")->count();
    }
}
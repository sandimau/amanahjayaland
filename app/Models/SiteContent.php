<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{
    protected $fillable = ['page', 'selector', 'kind', 'node_index', 'label', 'value'];

    public const PAGES = ['home', 'services', 'about', 'contact', 'gallery', 'project'];

    public const KINDS = ['text', 'image', 'link', 'background', 'placeholder'];
}

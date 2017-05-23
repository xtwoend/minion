<?php

namespace Minion\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Minion\Entities\Post;

class PostController extends Controller
{   
    protected $posts;
    public function __construct(Post $posts)
    {
        $this->posts = $posts;
    }

    public function index($slug)
    {
        $posts = $this->posts->publish()->paginate(10);
        return theme('posts', compact('posts'));
    }

    public function read($slug)
    {   
        try {
            $post = $this->posts->whereTranslation('slug', $slug)->firstOrFail();
            return theme('post', compact('post'));
        } catch (\Exception $e) {
            abort(404);   
        }
    }

    public function page($slug)
    {
        try {
            $page = $this->posts->whereTranslation('slug', $slug)->firstOrFail();
            return theme('page', compact('page'));
        } catch (\Exception $e) {
            abort(404);   
        }
    }
}

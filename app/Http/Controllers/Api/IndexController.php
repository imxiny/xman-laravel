<?php
/**
 * Created by PhpStorm.
 * Author: xinu x-php@outlook.com
 * Coding Standard: PSR2
 * DateTime: 2020-11-24 18:51
 */


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function index(Request $request): array
    {
        $keyword = $request->get('keyword');
        $query = DB::table('questions');
        if ($keyword) {
            $query->where('title', 'like', "%{$keyword}%")->orWhere('answer', 'like', "%{$keyword}%")->limit(20);
        }
        $list = $query->get();
        foreach ($list as $k => $v) {
            if ($keyword) {
                $v->title = str_replace($keyword, "<b style='color: red'>{$keyword}</b>", $v->title);
                $v->answer = str_replace($keyword, "<b style='color: red'>{$keyword}</b>", $v->answer);
            }
            $v->title = ($k + 1) . '. ' . $v->title;
            $v->answer = json_decode($v->answer, true);
        }
        return ['data' => $list];
    }
}

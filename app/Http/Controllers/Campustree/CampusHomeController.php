<?php

namespace App\Http\Controllers\Campustree;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Friend;
use App\Models\Participation;
use App\Models\Post;
use App\Models\Sex;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CampusHomeController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $currentuser = User::find($userId);
        $leaves = Post::all();
        $branches = Category::paginate(6);
        return view('campustree.home', [
            'leaves' => $leaves,
            'branches' => $branches,
            'user' => $currentuser
        ]);
    }

    public function showLeaf($id)
    {
        if(Auth::user()) {
            $friends = Auth::user()->friends()->get();
            $participation = Auth::user()->participations()->where('leaf_id', $id)->first();
            $comments = Comment::where('leaf_id', $id)->get();
            $leaf = Post::where('id', $id)->firstOrFail();
            return view('campustree.leaf', [
                'leaf' => $leaf,
                'comments' => $comments,
                'friends' => $friends,
                'participation' => $participation
            ]);
        }
        else {
            $comments = Comment::where('leaf_id', $id)->get();
            $leaf = Post::where('id', $id)->firstOrFail();
            return view('campustree.leaf', [
                'leaf' => $leaf,
                'comments' => $comments,
            ]);
        }


    }

    public function showBranch($id)
    {
        $branch = Category::where('id', $id)->firstOrFail();
        $thisBranchPost = Post::where('cat_id', $id)->get();
        return view('campustree.branch', [
            'branch' => $branch,
            'posts' => $thisBranchPost
        ]);
    }
    public function createLeaf()
    {
        $categories = Category::all();
        return view('campustree.create_leaf', [
            'categories' => $categories
        ]);
    }
    public function storeLeaf(Request $request)
    {
        $post = new Post;
        $post->title = $request->title;
        $post->text = $request->text;
        $post->cat_id = $request->cat_id;
        $post->img = $request->img;
        $post->save();
        return redirect()->back()->withSuccess('Post was successfully added');
    }

    public function personal($id)
    {
        $userId = Auth::id();
        $currentuser = User::find($id);
        $partIDs = Participation::where('user_id', $userId)->get();
        $arr = [];
        foreach ($partIDs as $id) {
            $arr[] = $id->leaf_id;
        }
        $postsArr = [];
        foreach ($arr as $a) {
            $postsArr[] = Post::where('id', $a)->first();
        }
        $branches = Category::paginate(6);
        return view('campustree.personal_page' , [
            'user' => $currentuser,
            'leaves' => $postsArr,
            'branches' => $branches
        ]);
    }

    public function addLeafToUser($id, Request $request){
        DB::table('participations')->insert([
            'user_id' => $request->user_id,
            'leaf_id' => $id
        ]);
        return redirect()->back();
    }

    public function deleteLeafFromUser($id, Request $request) {
        DB::table('participations')->where([
            'user_id' => $request->user_id,
            'leaf_id' => $id
        ])->delete();
        return redirect()->back();
    }

    public function allUsers(){
        $users = User::paginate(4);
        $cats = Category::all();
        $sexes = Sex::all();
        return view('campustree.users', [
            'users' => $users,
            'sexes' => $sexes,
            'cats' => $cats
        ]);
    }

    public function allFriends(){
        $users = Auth::user()->friends()->get();
        return view('campustree.friends', [
            'users' => $users,
        ]);
    }

    public function saveData(Request $request) {
        $user = new User;
        $user->name = $request->fname. ' ' .$request->lname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->user_bio = $request->description;
        $user->user_birth = $request->birthday;
//        $user->user_img = $request->user_img;
//        $user->cat_id = $request->cat_id;
//        $user->faculty_id = $request->cat_id;
        $user->sex_id = $request->sex;
        $user->save();
        $user->assignRole('user');
    }

//    public function search(){
//        // Check for search input
//        if (request('search')) {
//            $leaves = Post::where('title', 'like', '%' . request('search') . '%')->get();
//        } else {
//            $leaves = Post::all();
//        }
//        return view('campustree.home')->with('searchLeaves', $leaves);
//    }
}

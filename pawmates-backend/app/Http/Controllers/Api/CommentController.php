<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CommentController extends Controller
{
    // Commentaires d'un post
    public function byPost($postId)
    {
        return response()->json(
            Comment::with('user')
                ->where('post_id', $postId)
                ->whereNull('deleted_at')
                ->latest()->get()
        );
    }

    // Nombre de commentaires
    public function count($postId)
    {
        return response()->json(
            Comment::where('post_id', $postId)->whereNull('deleted_at')->count()
        );
    }

    // Mes commentaires
    public function myComments(Request $request)
    {
        return response()->json(
            Comment::with('post')
                ->where('user_id', $request->user()->id)
                ->whereNull('deleted_at')
                ->latest()->get()
        );
    }

    // Créer un commentaire
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string',
        ]);

        $post = Post::findOrFail($request->post_id);

        if ($post->status === 'removed') {
            return response()->json(['message' => 'Ce post n\'est plus disponible !'], 422);
        }

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'post_id' => $request->post_id,
            'content' => $request->content,
        ]);

        return response()->json($comment->load('user'), 201);
    }

    // Modifier un commentaire
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé !'], 403);
        }

        if ($comment->deleted_at !== null) {
            return response()->json(['message' => 'Ce commentaire a été supprimé !'], 422);
        }

        $comment->update(['content' => $request->getContent()]);
        return response()->json($comment);
    }

    // Supprimer (soft delete)
    public function destroy(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        $isAuthor   = $comment->user_id === $request->user()->id;
        $isOwner    = $comment->post->user_id === $request->user()->id;
        $isAdmin    = $request->user()->role === 'admin';

        if (!$isAuthor && !$isOwner && !$isAdmin) {
            return response()->json(['message' => 'Non autorisé !'], 403);
        }

        $comment->update(['deleted_at' => Carbon::now()]);
        return response()->json(['message' => 'Commentaire supprimé !']);
    }
}
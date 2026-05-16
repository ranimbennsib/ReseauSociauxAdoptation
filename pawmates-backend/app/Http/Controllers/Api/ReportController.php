<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Créer un signalement
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'reason'  => 'required|string',
        ]);

        $post = Post::findOrFail($request->post_id);

        if ($post->status === 'removed') {
            return response()->json(['message' => 'Ce post est déjà supprimé !'], 422);
        }

        if ($post->user_id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas signaler votre propre post !'], 422);
        }

        if (Report::where('post_id', $request->post_id)
            ->where('reporter_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Vous avez déjà signalé ce post !'], 422);
        }

        $report = Report::create([
            'post_id'     => $request->post_id,
            'reporter_id' => $request->user()->id,
            'reason'      => $request->reason,
            'status'      => 'pending',
        ]);

        return response()->json($report->load(['post', 'reporter']), 201);
    }

    // Tous les signalements (admin)
    public function index()
    {
        return response()->json(
            Report::with(['post', 'reporter'])->latest()->get()
        );
    }

    // Par status (admin)
    public function byStatus($status)
    {
        return response()->json(
            Report::with(['post', 'reporter'])
                ->where('status', $status)->latest()->get()
        );
    }

    // Par post (admin)
    public function byPost($postId)
    {
        return response()->json(
            Report::with('reporter')->where('post_id', $postId)->latest()->get()
        );
    }

    // Nombre par post (admin)
    public function countByPost($postId)
    {
        return response()->json(Report::where('post_id', $postId)->count());
    }

    // Mettre en cours de traitement (admin)
    public function review($id)
    {
        $report = Report::findOrFail($id);

        if ($report->status !== 'pending') {
            return response()->json(['message' => 'Ce signalement est déjà traité !'], 422);
        }

        $report->update(['status' => 'under_review']);
        return response()->json($report);
    }

    // Résoudre — supprimer le post (admin)
    public function resolve($id)
    {
        $report = Report::findOrFail($id);
        $report->update(['status' => 'resolved']);

        // Supprimer le post
        Post::where('id', $report->post_id)->update(['status' => 'removed']);

        // Résoudre tous les autres signalements du même post
        Report::where('post_id', $report->post_id)
            ->whereIn('status', ['pending', 'under_review'])
            ->update(['status' => 'resolved']);

        return response()->json($report);
    }

    // Ignorer (admin)
    public function dismiss($id)
    {
        $report = Report::findOrFail($id);

        if (in_array($report->status, ['resolved', 'dismissed'])) {
            return response()->json(['message' => 'Ce signalement est déjà traité !'], 422);
        }

        $report->update(['status' => 'dismissed']);
        return response()->json($report);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\AdoptionRequest;
use App\Models\Donation;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // ✅ Gestion des utilisateurs (corrigé)
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        
        // Ajouter les compteurs manuellement
        foreach ($users as $user) {
            $user->posts_count = Post::where('user_id', $user->id)->count();
            $user->adoption_requests_count = AdoptionRequest::where('requester_id', $user->id)->count();
            $user->donations_count = Donation::where('user_id', $user->id)->count();
        }
        
        return response()->json($users);
    }

    public function promote($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cet utilisateur est déjà admin'], 422);
        }
        
        $user->update(['role' => 'admin']);
        return response()->json($user);
    }

    public function demote($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->role === 'user') {
            return response()->json(['message' => 'Cet utilisateur est déjà un utilisateur normal'], 422);
        }
        
        $user->update(['role' => 'user']);
        return response()->json($user);
    }

    // ✅ Gestion des posts (corrigé)
    public function posts()
    {
        $posts = Post::with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Ajouter le compteur de signalements
        foreach ($posts as $post) {
            $post->reports_count = Report::where('post_id', $post->id)->count();
        }
        
        return response()->json($posts);
    }

    public function postsByStatus($status)
    {
        $posts = Post::with('user')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
        
        foreach ($posts as $post) {
            $post->reports_count = Report::where('post_id', $post->id)->count();
        }
        
        return response()->json($posts);
    }

    // ✅ Statistiques (corrigé)
    public function statistics()
    {
        $stats = [
            'total_users' => User::count(),
            'total_posts' => Post::count(),
            'total_adoptions' => Post::where('is_adoption', true)->count(),
            'total_donations' => Post::where('is_donation', true)->count(),
            'total_adoption_requests' => AdoptionRequest::count(),
            'total_donations_made' => Donation::sum('amount') ?? 0,
            'pending_reports' => Report::where('status', 'pending')->count(),
            'posts_by_status' => [
                'available' => Post::where('status', 'available')->count(),
                'adopted' => Post::where('status', 'adopted')->count(),
                'closed' => Post::where('status', 'closed')->count(),
                'removed' => Post::where('status', 'removed')->count(),
            ],
            'recent_posts' => Post::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'recent_users' => User::orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
        
        return response()->json($stats);
    }
}
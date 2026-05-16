<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DonationController extends Controller
{
    // Faire un don
    public function store(Request $request)
    {
        $request->validate([
            'post_id'          => 'required|exists:posts,id',
            'amount'           => 'required|numeric|min:1',
            'card_number'      => 'required|string|size:16',
            'card_holder_name' => 'required|string',
        ]);

        $post = Post::findOrFail($request->post_id);

        if (!$post->is_donation) {
            return response()->json(['message' => 'Ce post n\'accepte pas les donations !'], 422);
        }

        if ($post->status !== 'available') {
            return response()->json(['message' => 'Ce post n\'est plus disponible !'], 422);
        }

        if ($post->user_id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous faire un don à vous même !'], 422);
        }

        $donation = Donation::create([
            'post_id'                => $request->post_id,
            'user_id'                => $request->user()->id,
            'owner_id'               => $post->user_id,
            'donor_card_last_four'   => substr($request->card_number, -4),
            'donor_card_holder_name' => $request->card_holder_name,
            'amount'                 => $request->amount,
            'transaction_id'         => Str::uuid(),
        ]);

        return response()->json($donation->load(['post', 'user']), 201);
    }

    // Mes donations faites
    public function myDonations(Request $request)
    {
        return response()->json(
            Donation::with(['post', 'owner'])
                ->where('user_id', $request->user()->id)
                ->latest()->get()
        );
    }

    // Donations reçues
    public function received(Request $request)
    {
        return response()->json(
            Donation::with(['post', 'user'])
                ->where('owner_id', $request->user()->id)
                ->latest()->get()
        );
    }

    // Donations d'un post
    public function byPost(Request $request, $postId)
    {
        $post = Post::findOrFail($postId);

        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé !'], 403);
        }

        return response()->json(
            Donation::with('user')->where('post_id', $postId)->latest()->get()
        );
    }

    // Total des donations d'un post
    public function total(Request $request, $postId)
    {
        $post = Post::findOrFail($postId);

        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé !'], 403);
        }

        $total = Donation::where('post_id', $postId)->sum('amount');
        return response()->json(['total' => $total]);
    }

    // Nombre de donations
    public function count($postId)
    {
        return response()->json(
            Donation::where('post_id', $postId)->count()
        );
    }
}
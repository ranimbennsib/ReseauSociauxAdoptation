<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdoptionRequest;
use App\Models\Post;
use Illuminate\Http\Request;

class AdoptionRequestController extends Controller
{
    // Créer une demande
    public function store(Request $request)
    {
        $request->validate([
            'post_id'        => 'required|exists:posts,id',
            'message'        => 'required|string',
            'has_experience' => 'required|boolean',
            'housing_type'   => 'required|in:maison,appartement,villa,autre',
            'has_garden'     => 'required|boolean',
            'has_children'   => 'required|boolean',
            'phone_number'   => 'required|string',
        ]);

        $post = Post::findOrFail($request->post_id);

        if ($post->status !== 'available') {
            return response()->json(['message' => 'Ce post n\'est plus disponible !'], 422);
        }

        if (!$post->is_adoption) {
            return response()->json(['message' => 'Ce post n\'est pas une adoption !'], 422);
        }

        if ($post->user_id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas adopter votre propre animal !'], 422);
        }

        if (AdoptionRequest::where('post_id', $request->post_id)
            ->where('requester_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Vous avez déjà demandé ce post !'], 422);
        }

        $adoptionRequest = AdoptionRequest::create([
            'post_id'        => $request->post_id,
            'requester_id'   => $request->user()->id,
            'owner_id'       => $post->user_id,
            'message'        => $request->message,
            'has_experience' => $request->has_experience,
            'housing_type'   => $request->housing_type,
            'has_garden'     => $request->has_garden,
            'has_children'   => $request->has_children,
            'phone_number'   => $request->phone_number,
            'status'         => 'pending',
        ]);

        return response()->json($adoptionRequest->load(['post', 'requester']), 201);
    }

    // Demandes envoyées
    public function sent(Request $request)
    {
        return response()->json(
            AdoptionRequest::with(['post', 'owner'])
                ->where('requester_id', $request->user()->id)
                ->latest()->get()
        );
    }

    // Demandes reçues
    public function received(Request $request)
    {
        return response()->json(
            AdoptionRequest::with(['post', 'requester'])
                ->where('owner_id', $request->user()->id)
                ->latest()->get()
        );
    }

    // Demandes d'un post
    public function byPost(Request $request, $postId)
    {
        $post = Post::findOrFail($postId);

        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé !'], 403);
        }

        return response()->json(
            AdoptionRequest::with('requester')
                ->where('post_id', $postId)->latest()->get()
        );
    }

    // Accepter
    public function accept(Request $request, $id)
    {
        $adoptionRequest = AdoptionRequest::findOrFail($id);

        if ($adoptionRequest->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé !'], 403);
        }

        if ($adoptionRequest->status !== 'pending') {
            return response()->json(['message' => 'Cette demande n\'est plus en attente !'], 422);
        }

        $adoptionRequest->update(['status' => 'accepted']);

        AdoptionRequest::where('post_id', $adoptionRequest->post_id)
            ->where('id', '!=', $id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        Post::where('id', $adoptionRequest->post_id)
            ->update(['status' => 'adopted']);

        return response()->json($adoptionRequest);
    }

    // Refuser
    public function reject(Request $request, $id)
    {
        $adoptionRequest = AdoptionRequest::findOrFail($id);

        if ($adoptionRequest->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé !'], 403);
        }

        if ($adoptionRequest->status !== 'pending') {
            return response()->json(['message' => 'Cette demande n\'est plus en attente !'], 422);
        }

        $adoptionRequest->update(['status' => 'rejected']);
        return response()->json($adoptionRequest);
    }

    // Annuler
    public function cancel(Request $request, $id)
    {
        $adoptionRequest = AdoptionRequest::findOrFail($id);

        if ($adoptionRequest->requester_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé !'], 403);
        }

        if ($adoptionRequest->status !== 'pending') {
            return response()->json(['message' => 'Cette demande ne peut plus être annulée !'], 422);
        }

        $adoptionRequest->update(['status' => 'cancelled']);
        return response()->json($adoptionRequest);
    }
}
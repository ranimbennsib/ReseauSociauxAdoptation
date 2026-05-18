<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // Voir tous les posts disponibles
    public function index()
    {
        return response()->json(
            Post::with('user')->where('status', 'available')->latest()->get()
        );
    }

    // Voir un post
    public function show($id)
    {
        return response()->json(
            Post::with(['user', 'comments.user'])->findOrFail($id)
        );
    }

    // Mes posts
    public function myPosts(Request $request)
    {
        return response()->json(
            Post::where('user_id', $request->user()->id)->latest()->get()
        );
    }

    // Posts d'adoption
    public function adoption()
    {
        return response()->json(
            Post::with('user')->where('is_adoption', true)
                ->where('status', 'available')->latest()->get()
        );
    }

    // Posts de donation
    public function donation()
    {
        return response()->json(
            Post::with('user')->where('is_donation', true)
                ->where('status', 'available')->latest()->get()
        );
    }

    // Par catégorie
    public function byCategory($category)
    {
        return response()->json(
            Post::with('user')->where('category', $category)
                ->where('status', 'available')->latest()->get()
        );
    }

    // Par ville
    public function byCity($city)
    {
        return response()->json(
            Post::with('user')->where('city', $city)
                ->where('status', 'available')->latest()->get()
        );
    }

    // Créer un post
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'required|string',
            'category'    => 'required|string',
            'city'        => 'required|string',
            'is_adoption' => 'required|boolean',
            'is_donation' => 'required|boolean',
        ]);

        if (!$request->is_adoption && !$request->is_donation) {
            return response()->json([
                'message' => 'Le post doit être au moins une adoption ou une donation !'
            ], 422);
        }

        if ($request->is_donation && !$request->card_number) {
            return response()->json([
                'message' => 'Le numéro de carte est obligatoire pour une donation !'
            ], 422);
        }

        $post = Post::create([
            'user_id'             => $request->user()->id,
            'title'               => $request->title,
            'description'         => $request->description,
            'image'               => $request->image,
            'category'            => $request->category,
            'city'                => $request->city,
            'localisation_detail' => $request->localisation_detail,
            'is_adoption'         => $request->is_adoption,
            'is_donation'         => $request->is_donation,
            'card_number'         => $request->card_number,
            'card_holder_name'    => $request->card_holder_name,
            'status'              => 'available',
        ]);

        return response()->json($post->load('user'), 201);
    }

    // Modifier un post (propriétaire uniquement)
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // ✅ Vérification : seul le propriétaire peut modifier
        if ($post->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Non autorisé ! Vous n\'êtes pas le propriétaire de ce post.'
            ], 403);
        }

        // ✅ Vérification : on ne peut pas modifier un post fermé ou supprimé
        if (in_array($post->status, ['closed', 'removed', 'adopted'])) {
            return response()->json([
                'message' => 'Ce post ne peut plus être modifié (statut : ' . $post->status . ').'
            ], 422);
        }

        // ✅ Validation des champs (tous optionnels en modification)
        $request->validate([
            'title'               => 'sometimes|string|max:255',
            'description'         => 'sometimes|string',
            'image'               => 'sometimes|string',
            'category'            => 'sometimes|string',
            'city'                => 'sometimes|string',
            'localisation_detail' => 'sometimes|nullable|string',
            'is_adoption'         => 'sometimes|boolean',
            'is_donation'         => 'sometimes|boolean',
            'card_number'         => 'sometimes|nullable|string',
            'card_holder_name'    => 'sometimes|nullable|string',
        ]);

        // ✅ Vérification métier : au moins adoption ou donation
        $isAdoption = $request->has('is_adoption') ? $request->is_adoption : $post->is_adoption;
        $isDonation = $request->has('is_donation') ? $request->is_donation : $post->is_donation;

        if (!$isAdoption && !$isDonation) {
            return response()->json([
                'message' => 'Le post doit rester au moins une adoption ou une donation !'
            ], 422);
        }

        // ✅ Si donation activée, card_number obligatoire
        if ($isDonation && !$request->card_number && !$post->card_number) {
            return response()->json([
                'message' => 'Le numéro de carte est obligatoire pour une donation !'
            ], 422);
        }

        $post->update($request->only([
            'title', 'description', 'image', 'category',
            'city', 'localisation_detail', 'is_adoption',
            'is_donation', 'card_number', 'card_holder_name',
        ]));

        return response()->json($post->load('user'));
    }

    // Fermer un post (propriétaire uniquement)
    public function close(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé !'], 403);
        }

        if ($post->status === 'adopted') {
            return response()->json(['message' => 'Ce post est déjà adopté !'], 422);
        }

        $post->update(['status' => 'closed']);
        return response()->json($post);
    }

    // Supprimer (admin)
    public function remove($id)
    {
        $post = Post::findOrFail($id);
        $post->update(['status' => 'removed']);
        return response()->json($post);
    }
}
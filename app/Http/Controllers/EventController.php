<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $events = $user->events;
        } else {
            $events = Event::all();
        }
        
        return response()->json($events);
    }

    public function myEvents() {
        
        $events = Event::all();
        return response()->json($events);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'required',
            'location' => 'required|string|max:255',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:6144',
        ]);
        

        $data = $request->all();

        if ($request->hasFile('image')) {
            $filename = round(microtime(true)) . '-' . str_replace(' ', '-', $request->file('image')->getClientOriginalName());
            $request->file('image')->move(public_path('images'), $filename);
            $data['image'] = $filename;
        }
        // Add the user_id from the authenticated user
        $data['user_id'] = $request->user()->id;
        
        $event = Event::create($data);
        return response()->json($event, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $event = Event::findOrFail($id);
        return response()->json($event);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'required',
            'location' => 'required|string|max:255',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:6144',
        ]);

        $event = Event::findOrFail($id);
        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($event->image) {
                unlink(public_path('images/' . $event->image));
            }
            $filename = round(microtime(true)) . '-' . str_replace(' ', '-', $request->file('image')->getClientOriginalName());
            $request->file('image')->move(public_path('images'), $filename);
            $data['image'] = $filename;
        }

        $event->update($data);
        return response()->json($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $event = Event::findOrFail($id);
        
        // Delete image if exists
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();
        return response('Event deleted', 204);
    }
}

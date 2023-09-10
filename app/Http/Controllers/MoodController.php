<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class MoodController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $moods = Mood::all();

        return view('moods.index')
            ->with('moods', $moods);
    }

    /**
     * Show the form for creating a new resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('moods.create');
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'color' => 'required|string',
        ]);

        Mood::create($request->all());

        return redirect()->route('moods.index')
            ->with('success', 'Mood created.');
    }

    /**
     * Display the specified resource.
     * 
     * @param \App\Models\Mood;
     * @return \Illuminate\Http\Response
     */
    public function show(Mood $mood)
    {
        return view('moods.show')
            ->with('mood', $mood);
    }

    /**
     * Show the form for editing the specified resource.
     * 
     * @param \App\Models\Mood $mood
     * @return \Illuminate\Http\Response
     */
    public function edit(Mood $mood)
    {
        if ($mood->name == "Unknown") {
            return redirect()->route('moods.index')
                ->withErrors(['failed' => 'Unknown mood cannot be edited.']);
        }

        return view('moods.edit')
            ->with('mood', $mood);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Mood $mood
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mood $mood)
    {
        $request->validate([
            'name' => 'required|string',
            'color' => 'required|string',
        ]);

        if ($mood->name == "Unknown") {
            return redirect()->route('moods.show', [$mood->id])
                ->with('failed', 'Mood cannot be updated.');
        } else {
            $updatedMood = $request->all();
            $mood->update($updatedMood);
            Cache::forget('moods');

            return redirect()->route('moods.show', [$mood->id])
                ->with('success', 'Mood updated.');
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param \App\Models\Mood $mood
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mood $mood)
    {
        if ($mood->name == "Unknown") {
            return redirect()->route('moods.index')
                ->withErrors(['failed' => 'Unknown mood cannot be deleted.']);
        } else {
            $unknownMood = Mood::where('name', "Unknown")->first();

            Entry::where('mood_id', $mood->id)
                ->update(['mood_id' => $unknownMood->id]);

            $mood->delete();
            Cache::flush();

            return redirect()->route('moods.index')
                ->with('success', 'Mood deleted.');
        }
    }
}

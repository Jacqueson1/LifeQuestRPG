<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Character;
use Illuminate\Support\Carbon;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $character = Character::firstOrCreate(
            ['id' => 1],
            ['name' => 'Hero', 'level' => 1, 'xp' => 0, 'max_hp' => 100, 'hp' => 100],
        );

        $dailyTasks = Task::where('type', 'daily')->latest()->get();
        $todoTasks = Task::where('type', 'todo')->latest()->get();

        return view('tasks.index', compact('character', 'dailyTasks', 'todoTasks'));
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
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:daily,todo',
            'xp_reward' => 'required|integer|min:5|max:500',
        ]);

        Task::create($validated);

        return redirect()->route('tasks.index')->with('success', 'New quest added to your quest log!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Quest removed.');

    }

    public function complete(Request $request, Task $task)
    {
        if ($task->is_completed) {
            return redirect()->route('tasks.index');
        }

        $character = Character::firstOrCreate(['id' => 1]);
        if ($task->type === 'daily')
        {
            if ($task->completed_at && $task->completed_at->isYesterday()) {
                $task->streak += 1;
            } else {
                $task->streak = 1;
            }
        }
        $task->is_completed = true;
        $task->completed_at = Carbon::now();
        $task->save();

        $character->xp += $task->xp_reward;
        $leveledUp = false;
        $xpNeeded = $character->level * 100;

        while ($character->xp >= $xpNeeded) {
            $character->xp -= $xpNeeded;
            $character->level += 1;
            $leveledUp = true;
            $xpNeeded = $character->level * 100;
        }
        $character->save();


        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'earned_xp' => $task->xp_reward,
                'new_xp' => $character->xp,
                'new_level' => $character->level,
                'xp_needed' => $xpNeeded,
                'leveled_up' => $leveledUp,
                'streak' => $task->streak,
            ]);
        }

        return redirect()->route('tasks.index')->with('success', "+{$task->xp_reward} XP Earned!");
    }
}

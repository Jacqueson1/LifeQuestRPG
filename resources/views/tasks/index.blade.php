<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>LifeQuest RPG</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Anime.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

    <!-- Custom RPG Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/rpg.css') }}">
</head>
<body>
    <div class="container">
        <!-- 1. RPG HUD (Header) -->
        <div class="hud-card" id="hudCard">
            <div class="hud-header">
                <div>
                    <h1 class="hero-name">⚔️ {{ $character->name }}</h1>
                    <small style="color: var(--text-muted)">LifeQuest Adventurer</small>
                </div>
                <div class="level-badge" id="levelBadge">LVL {{ $character->level }}</div>
            </div>

            <!-- XP Progress Bar -->
            @php
                $xpNeeded = $character->level * 100;
                $xpPercent = min(100, round(($character->xp / $xpNeeded) * 100));
            @endphp
            <div class="bar-container">
                <div class="bar-label">
                    <span>XP Progress</span>
                    <span id="xpText">{{ $character->xp }} / {{ $xpNeeded }} XP ({{ $xpPercent }}%)</span>
                </div>
                <div class="bar-track">
                    <div class="bar-fill-xp" id="xpBar" style="width: {{ $xpPercent }}%;"></div>
                </div>
            </div>
        </div>

        <!-- 2. CREATE QUEST FORM (Validation + CSRF) -->
        <div class="form-card">
            <h3 style="margin-top: 0;">📜 Accept New Quest</h3>

            {{-- Validation Errors Display --}}
            @if ($errors->any())
                <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem;">
                    <ul style="margin: 0; padding-left: 1.25rem; color: #fca5a5;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <div class="input-group">
                    <input type="text" name="title" placeholder="Quest Title (e.g. Read 10 pages)" value="{{ old('title') }}" required>
                    <select name="type">
                        <option value="daily" {{ old('type') == 'daily' ? 'selected' : '' }}>Daily Quest</option>
                        <option value="todo" {{ old('type') == 'todo' ? 'selected' : '' }}>To-Do Quest</option>
                    </select>
                    <input type="number" name="xp_reward" placeholder="XP" value="{{ old('xp_reward', 25) }}" min="5" max="500">
                    <button type="submit" class="btn-gold">+ Accept Quest</button>
                </div>
            </form>
        </div>

        <!-- 3. DAILY QUESTS -->
        <div class="quest-section">
            <h2 class="section-title">☀️ Daily Quests (Build Streaks)</h2>
            @forelse ($dailyTasks as $task)
                <div class="quest-card {{ $task->is_completed ? 'completed' : '' }}" id="task-{{ $task->id }}">
                    <div class="quest-info">
                        <h4>{{ $task->title }}</h4>
                        <div class="quest-meta">
                            <span class="xp-pill">+{{ $task->xp_reward }} XP</span>
                            <span class="streak-pill">🔥 {{ $task->streak }} Day Streak</span>
                        </div>
                    </div>
                    <div class="quest-actions">
                        @if (!$task->is_completed)
                            <button class="btn-complete" onclick="completeQuest({{ $task->id }}, event)">✓ Complete</button>
                        @endif
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Abandon this quest?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete">✕</button>
                        </form>
                    </div>
                </div>
            @empty
                <p style="color: var(--text-muted);">No daily quests active. Add one above to build your streak!</p>
            @endforelse
        </div>

        <!-- 4. TO-DO QUESTS -->
        <div class="quest-section">
            <h2 class="section-title">🎯 To-Do Quests</h2>
            @forelse ($todoTasks as $task)
                <div class="quest-card {{ $task->is_completed ? 'completed' : '' }}" id="task-{{ $task->id }}">
                    <div class="quest-info">
                        <h4>{{ $task->title }}</h4>
                        <div class="quest-meta">
                            <span class="xp-pill">+{{ $task->xp_reward }} XP</span>
                        </div>
                    </div>
                    <div class="quest-actions">
                        @if (!$task->is_completed)
                            <button class="btn-complete" onclick="completeQuest({{ $task->id }}, event)">✓ Complete</button>
                        @endif
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Abandon this quest?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete">✕</button>
                        </form>
                    </div>
                </div>
            @empty
                <p style="color: var(--text-muted);">No to-do quests right now. Adventure awaits!</p>
            @endforelse
        </div>
    </div>

    <!-- Custom RPG Javascript -->
    <script src="{{ asset('js/rpg.js') }}"></script>
</body>
</html>

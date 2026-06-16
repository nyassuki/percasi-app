<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<!-- Gunakan CDN Tailwind jika belum tersedia -->
<?php if (!isset($tailwind_loaded)): ?>
<script src="https://cdn.tailwindcss.com"></script>
<?php endif; ?>

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
    }
    
    @keyframes pulse-ring {
        0% { transform: scale(0.95); opacity: 0.7; }
        100% { transform: scale(1.1); opacity: 0; }
    }
    
    @keyframes slide-up {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .match-card:hover .floating-icon {
        animation: float 2s ease-in-out infinite;
    }
    
    .live-badge::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 8px;
        background: #ef4444;
        animation: pulse-ring 1.5s cubic-bezier(0.215, 0.610, 0.355, 1) infinite;
    }
    
    .match-card {
        animation: slide-up 0.3s ease-out;
        animation-fill-mode: both;
    }
    
    /* Custom Scrollbar */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb { 
        background: linear-gradient(to bottom, #10b981, #059669);
        border-radius: 10px; 
    }
    ::-webkit-scrollbar-thumb:hover { background: linear-gradient(to bottom, #059669, #047857); }
    
    /* Loading shimmer effect */
    .shimmer {
        background: linear-gradient(90deg, 
            rgba(255, 255, 255, 0) 0%, 
            rgba(255, 255, 255, 0.6) 50%, 
            rgba(255, 255, 255, 0) 100%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }
    
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    
    /* Chess piece animation */
    .chess-piece {
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        transition: all 0.3s ease;
    }
    
    .match-card:hover .chess-piece {
        filter: drop-shadow(0 8px 15px rgba(0, 0, 0, 0.2));
        transform: scale(1.1);
    }
    
    /* Smooth transitions */
    .smooth-transition {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="relative">
                        <div class="absolute -inset-1 bg-emerald-500 rounded-full blur opacity-20"></div>
                        <div class="relative w-4 h-8 bg-gradient-to-b from-emerald-500 to-emerald-600 rounded-full"></div>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-black text-slate-800 uppercase tracking-tight">
                        LIVE <span class="text-emerald-500 relative">
                            MATCHES
                            <span class="absolute -bottom-1 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 to-transparent opacity-50 rounded-full"></span>
                        </span>
                    </h1>
                </div>
                <div class="flex items-center gap-4 ml-6">
                    <p class="text-slate-500 text-sm font-medium">
                        Tonton pertandingan berlangsung secara real-time
                    </p>
                    <div id="connectionStatus" class="flex items-center gap-2 hidden">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                        <span class="text-xs font-medium text-emerald-600">Connected</span>
                    </div>
                </div>
                <div id="lastUpdated" class="ml-6 mt-2 text-xs text-slate-400 font-medium"></div>
            </div>

            <div class="flex items-center gap-3">
                <div id="matchCountBadge" class="flex items-center gap-2 px-4 py-2 bg-white backdrop-blur-sm rounded-xl border border-slate-200 shadow-sm">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span id="matchCount" class="text-xs font-bold text-slate-700">0 Matches Live</span>
                </div>
                <button id="refreshBtn" onclick="fetchLive()" class="smooth-transition px-4 py-2 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-900 hover:to-slate-950 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
        
        <!-- Stats Bar -->
        <div id="statsBar" class="hidden mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-white to-slate-50 rounded-xl border border-slate-200 p-4">
                <div class="text-2xl font-black text-slate-800" id="totalMoves">0</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Moves</div>
            </div>
            <div class="bg-gradient-to-br from-white to-slate-50 rounded-xl border border-slate-200 p-4">
                <div class="text-2xl font-black text-emerald-600" id="avgTime">00:00</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Avg. Game Time</div>
            </div>
            <div class="bg-gradient-to-br from-white to-slate-50 rounded-xl border border-slate-200 p-4">
                <div class="text-2xl font-black text-blue-600" id="whiteWins">0</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">White Wins</div>
            </div>
            <div class="bg-gradient-to-br from-white to-slate-50 rounded-xl border border-slate-200 p-4">
                <div class="text-2xl font-black text-slate-800" id="blackWins">0</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Black Wins</div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div id="matchesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-12">
        <div class="inline-block relative">
            <div class="absolute inset-0 bg-emerald-500 rounded-full blur-xl opacity-20 animate-pulse"></div>
            <div class="relative w-16 h-16 border-4 border-slate-200 border-t-emerald-500 rounded-full animate-spin"></div>
        </div>
        <p class="mt-4 text-slate-600 font-medium">Memuat pertandingan live...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-16">
        <div class="relative inline-block mb-6">
            <div class="absolute inset-0 bg-slate-200 rounded-full blur-xl"></div>
            <div class="relative w-24 h-24 bg-gradient-to-br from-white to-slate-50 border-4 border-slate-200 rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </div>
        </div>
        <h3 class="text-xl font-bold text-slate-700 mb-2">Belum ada pertandingan aktif</h3>
        <p class="text-slate-500 text-sm mb-6">Nantikan pertandingan catur live berikutnya!</p>
        <button onclick="fetchLive()" class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-all">
            Refresh Halaman
        </button>
    </div>

    <!-- Error State -->
    <div id="errorState" class="hidden text-center py-16">
        <div class="relative inline-block mb-6">
            <div class="absolute inset-0 bg-red-500 rounded-full blur-xl opacity-20"></div>
            <div class="relative w-24 h-24 bg-gradient-to-br from-white to-slate-50 border-4 border-slate-200 rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <h3 class="text-xl font-bold text-slate-700 mb-2">Gagal memuat data</h3>
        <p class="text-slate-500 text-sm mb-4" id="errorMessage">Terjadi kesalahan saat mengambil data pertandingan.</p>
        <button onclick="fetchLive()" class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-all">
            Coba Lagi
        </button>
    </div>

    <!-- Footer -->
    <div id="footerInfo" class="hidden mt-12 pt-8 border-t border-slate-200">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-700">Real-time Updates</p>
                    <p class="text-xs text-slate-500">Data diperbarui otomatis setiap 10 detik</p>
                </div>
            </div>
            <div class="text-xs text-slate-400">
                <span id="lastRefreshTime">--:--:--</span> • 
                <span id="matchesLoaded">0</span> pertandingan dimuat
            </div>
        </div>
    </div>
</div>

<script>
    const API_URL = 'https://backend.catur.cloud/api/matches/live'; // Ganti dengan URL API yang sesuai
    const BASE_URL = '<?= base_url() ?>';
    let refreshInterval = null;
    let isFetching = false;

    // Elemen DOM
    const elements = {
        matchesGrid: document.getElementById('matchesGrid'),
        matchCount: document.getElementById('matchCount'),
        matchCountBadge: document.getElementById('matchCountBadge'),
        lastUpdated: document.getElementById('lastUpdated'),
        footerInfo: document.getElementById('footerInfo'),
        loadingState: document.getElementById('loadingState'),
        emptyState: document.getElementById('emptyState'),
        errorState: document.getElementById('errorState'),
        errorMessage: document.getElementById('errorMessage'),
        refreshBtn: document.getElementById('refreshBtn'),
        connectionStatus: document.getElementById('connectionStatus'),
        lastRefreshTime: document.getElementById('lastRefreshTime'),
        matchesLoaded: document.getElementById('matchesLoaded'),
        statsBar: document.getElementById('statsBar'),
        totalMoves: document.getElementById('totalMoves'),
        avgTime: document.getElementById('avgTime'),
        whiteWins: document.getElementById('whiteWins'),
        blackWins: document.getElementById('blackWins')
    };

    // Tampilkan state loading
    function showLoading() {
        elements.loadingState.classList.remove('hidden');
        elements.emptyState.classList.add('hidden');
        elements.errorState.classList.add('hidden');
        elements.matchesGrid.innerHTML = '';
        elements.statsBar.classList.add('hidden');
    }

    // Tampilkan empty state
    function showEmptyState() {
        elements.loadingState.classList.add('hidden');
        elements.emptyState.classList.remove('hidden');
        elements.errorState.classList.add('hidden');
        elements.matchesGrid.innerHTML = '';
        elements.footerInfo.classList.add('hidden');
        elements.statsBar.classList.add('hidden');
        elements.matchCountBadge.classList.remove('bg-emerald-50', 'border-emerald-200');
        elements.matchCountBadge.classList.add('bg-amber-50', 'border-amber-200');
    }

    // Tampilkan error state
    function showErrorState(message) {
        elements.loadingState.classList.add('hidden');
        elements.emptyState.classList.add('hidden');
        elements.errorState.classList.remove('hidden');
        elements.matchesGrid.innerHTML = '';
        elements.footerInfo.classList.add('hidden');
        elements.statsBar.classList.add('hidden');
        elements.errorMessage.textContent = message || 'Terjadi kesalahan saat mengambil data pertandingan.';
        elements.matchCountBadge.classList.remove('bg-emerald-50', 'border-emerald-200');
        elements.matchCountBadge.classList.add('bg-red-50', 'border-red-200');
    }

    // Format waktu
    function formatTime(dateString) {
        if (!dateString) return '--:--';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '--:--';
        
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        
        if (diffMins < 1) return 'Baru saja';
        if (diffMins < 60) return `${diffMins}m yang lalu`;
        
        return date.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: false 
        });
    }

    // Format durasi
    function formatDuration(seconds) {
        if (!seconds) return '00:00';
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    // Hitung statistik
    function calculateStats(matches) {
        const stats = {
            totalMoves: 0,
            totalTime: 0,
            whiteWins: 0,
            blackWins: 0,
            totalGames: matches.length
        };

        matches.forEach(match => {
            stats.totalMoves += match.move_count || 0;
            if (match.game_time) {
                stats.totalTime += match.game_time;
            }
            if (match.result === '1-0') stats.whiteWins++;
            if (match.result === '0-1') stats.blackWins++;
        });

        return stats;
    }

    // Update statistik UI
    function updateStats(stats) {
        elements.statsBar.classList.remove('hidden');
        elements.totalMoves.textContent = stats.totalMoves;
        elements.avgTime.textContent = stats.totalGames > 0 ? 
            formatDuration(Math.floor(stats.totalTime / stats.totalGames)) : '00:00';
        elements.whiteWins.textContent = stats.whiteWins;
        elements.blackWins.textContent = stats.blackWins;
    }

    // Render kartu pertandingan
    function renderMatchCard(match, index) {
        const player1 = match.white_player || 'Player 1';
        const player2 = match.black_player || 'Player 2';
        const moves = match.move_count || 0;
        const gameTime = match.game_time || 0;
        const result = match.result || '*';
        
        // Determine result colors
        let resultColor = 'text-slate-700';
        let resultBg = 'bg-slate-100';
        if (result === '1-0') {
            resultColor = 'text-emerald-700';
            resultBg = 'bg-emerald-100';
        } else if (result === '0-1') {
            resultColor = 'text-slate-800';
            resultBg = 'bg-slate-200';
        } else if (result === '1/2-1/2') {
            resultColor = 'text-amber-700';
            resultBg = 'bg-amber-100';
        }

        return `
            <div class="match-card" style="animation-delay: ${index * 0.1}s">
                <div class="group relative bg-gradient-to-br from-white to-slate-50 rounded-3xl border border-slate-200 p-6 transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl hover:border-emerald-200/50">
                    <!-- Live Badge -->
                    <div class="absolute -top-3 left-6">
                        <div class="relative live-badge">
                            <div class="relative px-4 py-1.5 bg-gradient-to-r from-red-500 to-red-600 text-white text-[10px] font-black rounded-xl uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                                LIVE NOW
                            </div>
                        </div>
                    </div>

                    <!-- Match Info -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="text-xs font-bold text-slate-400 bg-slate-100 px-3 py-1 rounded-full">MATCH #${match.id}</span>
                            <p class="text-xs text-slate-500 mt-1">
                                ${match.tournament_name || 'Friendly Match'} • ${match.time_control || 'Rapid'}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-semibold ${resultColor} ${resultBg} px-2 py-1 rounded-lg">
                                ${result}
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1">
                                Mulai: ${formatTime(match.started_at)}
                            </div>
                        </div>
                    </div>

                    <!-- Players -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between gap-4">
                            <!-- White Player -->
                            <div class="text-center flex-1">
                                <div class="relative w-16 h-16 mx-auto bg-gradient-to-br from-white to-slate-100 border-2 border-slate-300 rounded-full flex items-center justify-center mb-3 shadow-sm">
                                    <span class="text-2xl chess-piece">♔</span>
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-white border border-slate-300 rounded-full flex items-center justify-center">
                                        <span class="text-xs font-bold text-emerald-600">${match.white_rating || '?'}</span>
                                    </div>
                                </div>
                                <p class="text-sm font-bold text-slate-800 truncate px-2">${player1}</p>
                                <p class="text-[10px] font-semibold text-emerald-600 uppercase tracking-widest mt-1">White</p>
                            </div>
                            
                            <!-- VS -->
                            <div class="px-2">
                                <div class="w-12 h-12 bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl flex items-center justify-center mb-1 mx-auto shadow-lg">
                                    <span class="text-sm font-black text-white italic">VS</span>
                                </div>
                                <div class="text-center mt-2">
                                    <div class="text-xl font-black text-emerald-600">${moves}</div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase">Moves</div>
                                </div>
                            </div>

                            <!-- Black Player -->
                            <div class="text-center flex-1">
                                <div class="relative w-16 h-16 mx-auto bg-gradient-to-br from-slate-800 to-slate-900 border-2 border-slate-700 rounded-full flex items-center justify-center mb-3 shadow-lg">
                                    <span class="text-2xl text-white chess-piece">♚</span>
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-white border border-slate-300 rounded-full flex items-center justify-center">
                                        <span class="text-xs font-bold text-slate-800">${match.black_rating || '?'}</span>
                                    </div>
                                </div>
                                <p class="text-sm font-bold text-slate-800 truncate px-2">${player2}</p>
                                <p class="text-[10px] font-semibold text-slate-600 uppercase tracking-widest mt-1">Black</p>
                            </div>
                        </div>

                        <!-- Game Progress -->
                        <div class="mt-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-slate-600">Game Progress</span>
                                <span class="text-xs font-bold text-emerald-600">${formatDuration(gameTime)}</span>
                            </div>
                            <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-full" 
                                     style="width: ${Math.min((moves / 80) * 100, 100)}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <button onclick="window.location.href='${BASE_URL}admin/matches/mirror/${match.id}'" 
                            class="relative w-full overflow-hidden bg-gradient-to-r from-slate-800 to-slate-900 hover:from-emerald-600 hover:to-emerald-700 text-white py-3.5 rounded-2xl font-bold text-xs uppercase tracking-[0.2em] transition-all duration-300 active:scale-95 shadow-md hover:shadow-lg group">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-transparent opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                        <span class="relative flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Tonton Pertandingan
                        </span>
                    </button>
                </div>
            </div>
        `;
    }

    // Fetch data dari API
    async function fetchLive() {
        if (isFetching) return;
        
        isFetching = true;
        elements.refreshBtn.disabled = true;
        elements.refreshBtn.innerHTML = `
            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Loading...
        `;
        
        showLoading();
        
        try {
            const response = await fetch(API_URL, {
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            const matches = result.data || [];
            
            // Update UI
            elements.matchCount.textContent = `${matches.length} Match${matches.length !== 1 ? 'es' : ''} Live`;
            elements.matchesLoaded.textContent = matches.length;
            
            const now = new Date();
            elements.lastUpdated.textContent = `Updated: ${now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            })}`;
            elements.lastRefreshTime.textContent = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            
            if (matches.length === 0) {
                showEmptyState();
            } else {
                elements.loadingState.classList.add('hidden');
                elements.footerInfo.classList.remove('hidden');
                elements.matchesGrid.innerHTML = matches.map((match, index) => renderMatchCard(match, index)).join('');
                
                // Update stats
                const stats = calculateStats(matches);
                updateStats(stats);
                
                // Update badge color
                elements.matchCountBadge.classList.remove('bg-amber-50', 'border-amber-200', 'bg-red-50', 'border-red-200');
                elements.matchCountBadge.classList.add('bg-emerald-50', 'border-emerald-200');
                elements.connectionStatus.classList.remove('hidden');
            }
            
        } catch (error) {
            console.error('Fetch error:', error);
            showErrorState(`Gagal memuat data: ${error.message}`);
        } finally {
            isFetching = false;
            elements.refreshBtn.disabled = false;
            elements.refreshBtn.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            `;
        }
    }

    // Inisialisasi
    window.addEventListener('DOMContentLoaded', () => {
        fetchLive();
        
        // Set interval refresh setiap 10 detik
        refreshInterval = setInterval(fetchLive, 10000);
        
        // Tambahkan keyboard shortcut (Ctrl+R atau Cmd+R)
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                fetchLive();
            }
        });
        
        // Tampilkan tooltip untuk badge
        elements.matchCountBadge.title = 'Klik untuk refresh';
        elements.matchCountBadge.style.cursor = 'pointer';
        elements.matchCountBadge.addEventListener('click', fetchLive);
    });

    // Clean up interval
    window.addEventListener('beforeunload', () => {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });

    // Auto-refresh saat tab menjadi aktif
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            fetchLive();
        }
    });
</script>

<?= $this->endSection() ?>
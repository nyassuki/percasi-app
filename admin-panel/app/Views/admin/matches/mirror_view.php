<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<!-- Load Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chessboard-js/1.0.0/chessboard-1.0.0.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chessboard-js/1.0.0/chessboard-1.0.0.min.js"></script>
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>

<style>
    /* Modern minimalist styles */
    .board-container {
        max-width: 700px;
        margin: 0 auto;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
    
    .player-card {
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .move-history-item {
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    
    .move-history-item:hover {
        border-left-color: #10b981;
        background: rgba(16, 185, 129, 0.05);
    }
    
    .active-timer {
        font-feature-settings: "tnum";
        font-variant-numeric: tabular-nums;
    }
    
    /* Custom board colors */
    .white-square {
        background-color: #f0f0f0;
    }
    
    .black-square {
        background-color: #769656;
    }
    
    /* Animation for live indicator */
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .pulse {
        animation: pulse 2s infinite;
    }
    
    /* Scrollbar styling */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #1f2937;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #4b5563;
        border-radius: 3px;
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-900 to-slate-950 text-white">
    <!-- Header -->
    <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <a href="<?= base_url('admin/matches') ?>" 
                   class="p-2 bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors">
                    ← Back
                </a>
                <div>
                    <h1 class="text-2xl font-bold">Match #<?= htmlspecialchars($matchId) ?></h1>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="w-2 h-2 bg-red-500 rounded-full pulse"></div>
                        <span class="text-sm text-slate-300">Live Viewing</span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold" id="spectatorCount">0</div>
                    <div class="text-xs text-slate-400">Spectators</div>
                </div>
                <button id="flipBoardBtn" 
                        class="p-2 bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors">
                    Flip Board
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Game Info -->
            <div class="space-y-6">
                <!-- Black Player -->
                <div class="player-card bg-slate-800/50 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-black rounded-full flex items-center justify-center border-2 border-slate-600">
                                <span class="text-xl">♚</span>
                            </div>
                            <div>
                                <div id="blackPlayerName" class="font-semibold">Loading...</div>
                                <div class="text-sm text-slate-400">Black</div>
                            </div>
                        </div>
                        <div id="blackRating" class="px-3 py-1 bg-black text-white text-sm font-medium rounded-full">
                            ?
                        </div>
                    </div>
                    <div id="blackTimer" class="text-3xl font-mono font-bold text-center active-timer">
                        10:00
                    </div>
                </div>

                <!-- Game Status -->
                <div class="player-card bg-slate-800/50 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold">Game Status</h3>
                        <div id="statusIndicator" class="w-2 h-2 bg-green-500 rounded-full pulse"></div>
                    </div>
                    <div id="gameStatus" class="text-xl font-bold text-green-400">In Progress</div>
                    <div id="gameResult" class="text-sm text-slate-300 mt-2 hidden"></div>
                </div>

                <!-- Game Details -->
                <div class="player-card bg-slate-800/50 rounded-xl p-6">
                    <h3 class="font-semibold mb-4">Game Details</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Time Control:</span>
                            <span id="timeControl" class="font-medium">10+0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Game Type:</span>
                            <span id="gameType" class="font-medium">Rapid</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Started:</span>
                            <span id="startedAt" class="font-medium">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Center Column - Chess Board -->
            <div class="lg:col-span-2">
                <!-- Chess Board -->
                <div class="board-container mb-8">
                    <div id="board" style="width: 100%"></div>
                </div>

                <!-- Board Controls -->
                <div class="flex justify-center gap-4 mb-8">
                    <button id="prevMove" 
                            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors">
                        Previous Move
                    </button>
                    <button id="nextMove" 
                            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors">
                        Next Move
                    </button>
                    <button onclick="fetchMatchDetails()" 
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                        Refresh
                    </button>
                </div>

                <!-- White Player -->
                <div class="player-card bg-slate-800/50 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center border-2 border-slate-300">
                                <span class="text-xl text-black">♔</span>
                            </div>
                            <div>
                                <div id="whitePlayerName" class="font-semibold">Loading...</div>
                                <div class="text-sm text-slate-400">White</div>
                            </div>
                        </div>
                        <div id="whiteRating" class="px-3 py-1 bg-white text-black text-sm font-medium rounded-full">
                            ?
                        </div>
                    </div>
                    <div id="whiteTimer" class="text-3xl font-mono font-bold text-center active-timer text-emerald-400">
                        10:00
                    </div>
                </div>
            </div>

            <!-- Right Column - Move History -->
            <div class="space-y-6">
                <div class="player-card bg-slate-800/50 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold">Move History</h3>
                        <div class="text-sm text-slate-400" id="moveCount">0 moves</div>
                    </div>
                    
                    <div id="moveHistory" class="custom-scrollbar space-y-1 max-h-96 overflow-y-auto">
                        <div class="text-center py-8 text-slate-500">
                            Waiting for first move...
                        </div>
                    </div>
                    
                    <div class="flex gap-2 mt-4">
                        <button id="copyPGN" 
                                class="flex-1 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-sm rounded-lg transition-colors">
                            Copy PGN
                        </button>
                        <button id="clearHistory" 
                                class="flex-1 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-sm rounded-lg transition-colors">
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Connection Status -->
                <div class="player-card bg-slate-800/50 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold">Connection</h3>
                        <div class="flex items-center gap-2">
                            <div id="connectionDot" class="w-2 h-2 bg-red-500 rounded-full"></div>
                            <span id="connectionText" class="text-sm">Connecting...</span>
                        </div>
                    </div>
                    
                    <button id="reconnectBtn" 
                            class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors hidden">
                        Reconnect
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-4 right-4 bg-slate-800 border border-slate-700 text-white px-4 py-3 rounded-lg shadow-lg hidden z-50">
    <div class="flex items-center gap-3">
        <div id="toastIcon"></div>
        <div id="toastMessage" class="text-sm"></div>
        <button onclick="hideToast()" class="text-slate-400 hover:text-white ml-4">
            ✕
        </button>
    </div>
</div>

<script>
    // Variables
    const matchId = "<?= htmlspecialchars($matchId) ?>";
    const baseUrl = "<?= base_url() ?>";
    const API_URL = `https://192.168.1.13:3000/api/matches/${matchId}`;
    const WS_URL = 'https://192.168.1.13:3000';
    
    let game = new Chess();
    let board = null;
    let socket = null;
    let moveHistory = [];
    let currentMoveIndex = -1;
    let boardOrientation = 'white';
    let whiteTime = 600;
    let blackTime = 600;
    let timerInterval = null;

    // Initialize chessboard
    function initChessboard() {
        board = Chessboard('board', {
            position: 'start',
            orientation: boardOrientation,
            showNotation: true,
            draggable: false,
            pieceTheme: 'https://chessboardjs.com/img/chesspieces/wikipedia/{piece}.png'
        });
    }

    // Connect to WebSocket
    function connectWebSocket() {
        if (socket) socket.disconnect();
        
        socket = io(WS_URL, {
            transports: ['websocket', 'polling']
        });
        
        socket.on('connect', () => {
            showToast('Connected to live match', 'success');
            socket.emit('joinMatch', matchId);
            updateConnectionStatus(true);
        });
        
        socket.on('disconnect', () => {
            updateConnectionStatus(false);
            showToast('Disconnected', 'error');
        });
        
        socket.on('matchState', updateGameState);
        socket.on('moveMade', handleMove);
        socket.on('gameOver', handleGameOver);
        socket.on('spectatorUpdate', (count) => {
            document.getElementById('spectatorCount').textContent = count;
        });
        
        socket.on('timeUpdate', (times) => {
            if (times.white) updateTimer('white', times.white);
            if (times.black) updateTimer('black', times.black);
        });
    }

    // Update game state
    function updateGameState(data) {
        if (data.fen) {
            game.load(data.fen);
            board.position(data.fen);
        }
        
        if (data.white_player) {
            document.getElementById('whitePlayerName').textContent = data.white_player;
        }
        if (data.black_player) {
            document.getElementById('blackPlayerName').textContent = data.black_player;
        }
        if (data.white_rating) {
            document.getElementById('whiteRating').textContent = data.white_rating;
        }
        if (data.black_rating) {
            document.getElementById('blackRating').textContent = data.black_rating;
        }
        
        // Update timers
        if (data.white_time) updateTimer('white', data.white_time);
        if (data.black_time) updateTimer('black', data.black_time);
        
        // Update move history
        if (data.moves && data.moves.length > 0) {
            data.moves.forEach(move => {
                addMoveToHistory(move);
            });
        }
        
        // Update game info
        if (data.time_control) {
            document.getElementById('timeControl').textContent = data.time_control;
        }
        if (data.game_type) {
            document.getElementById('gameType').textContent = data.game_type;
        }
        if (data.started_at) {
            document.getElementById('startedAt').textContent = new Date(data.started_at).toLocaleTimeString();
        }
    }

    // Handle move
    function handleMove(moveData) {
        try {
            const move = game.move({
                from: moveData.from,
                to: moveData.to,
                promotion: moveData.promotion || 'q'
            });
            
            if (move) {
                board.position(game.fen());
                addMoveToHistory(move.san);
                updateMoveCount();
                
                if (game.game_over()) {
                    determineGameResult();
                }
            }
        } catch (error) {
            console.error('Invalid move:', error);
        }
    }

    // Add move to history
    function addMoveToHistory(move) {
        const historyDiv = document.getElementById('moveHistory');
        
        if (historyDiv.querySelector('.text-center')) {
            historyDiv.innerHTML = '';
        }
        
        const moveNumber = Math.floor(game.history().length / 2) + 1;
        const isWhiteMove = game.history().length % 2 === 1;
        
        if (isWhiteMove) {
            const row = document.createElement('div');
            row.className = 'move-history-item p-2 rounded';
            row.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="text-slate-500 text-sm w-8">${moveNumber}.</span>
                    <span class="bg-blue-900/50 text-blue-200 px-3 py-1 rounded text-sm flex-1 text-center">${move}</span>
                    <span class="text-slate-500 text-sm w-8"></span>
                </div>
            `;
            historyDiv.appendChild(row);
        } else {
            const lastRow = historyDiv.lastElementChild;
            if (lastRow) {
                const blackMoveSpan = lastRow.querySelector('span:nth-child(3)');
                blackMoveSpan.className = 'bg-slate-800 text-white px-3 py-1 rounded text-sm flex-1 text-center';
                blackMoveSpan.textContent = move;
            }
        }
        
        historyDiv.scrollTop = historyDiv.scrollHeight;
        moveHistory = game.history();
        currentMoveIndex = moveHistory.length - 1;
    }

    // Update move count
    function updateMoveCount() {
        const count = game.history().length;
        document.getElementById('moveCount').textContent = `${count} moves`;
    }

    // Update timer
    function updateTimer(player, seconds) {
        if (player === 'white') whiteTime = seconds;
        if (player === 'black') blackTime = seconds;
        
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;
        const formatted = `${minutes}:${secs.toString().padStart(2, '0')}`;
        
        const element = document.getElementById(`${player}Timer`);
        element.textContent = formatted;
        
        if (seconds < 60) {
            element.classList.add('text-red-500', 'animate-pulse');
            element.classList.remove(player === 'white' ? 'text-emerald-400' : 'text-white');
        } else {
            element.classList.remove('text-red-500', 'animate-pulse');
            element.classList.add(player === 'white' ? 'text-emerald-400' : 'text-white');
        }
    }

    // Handle game over
    function handleGameOver(result) {
        const statusElement = document.getElementById('gameStatus');
        const resultElement = document.getElementById('gameResult');
        
        let statusText = '';
        let resultText = '';
        
        switch(result) {
            case '1-0':
                statusText = 'White Wins';
                resultText = 'White wins by checkmate';
                break;
            case '0-1':
                statusText = 'Black Wins';
                resultText = 'Black wins by checkmate';
                break;
            case '1/2-1/2':
                statusText = 'Draw';
                resultText = 'Draw by agreement';
                break;
            default:
                statusText = 'Game Over';
                resultText = result;
        }
        
        statusElement.textContent = statusText;
        resultElement.textContent = resultText;
        resultElement.classList.remove('hidden');
        
        clearInterval(timerInterval);
    }

    // Determine game result
    function determineGameResult() {
        if (game.in_checkmate()) {
            handleGameOver(game.turn() === 'w' ? '0-1' : '1-0');
        } else if (game.in_draw() || game.in_stalemate()) {
            handleGameOver('1/2-1/2');
        }
    }

    // Update connection status
    function updateConnectionStatus(connected) {
        const dot = document.getElementById('connectionDot');
        const text = document.getElementById('connectionText');
        const reconnectBtn = document.getElementById('reconnectBtn');
        
        if (connected) {
            dot.className = 'w-2 h-2 bg-green-500 rounded-full pulse';
            text.textContent = 'Connected';
            reconnectBtn.classList.add('hidden');
        } else {
            dot.className = 'w-2 h-2 bg-red-500 rounded-full';
            text.textContent = 'Disconnected';
            reconnectBtn.classList.remove('hidden');
        }
    }

    // Flip board
    function flipBoard() {
        boardOrientation = boardOrientation === 'white' ? 'black' : 'white';
        board.orientation(boardOrientation);
        showToast(`Board flipped to ${boardOrientation} view`);
    }

    // Navigate moves
    function navigateMoves(direction) {
        if (direction === 'prev' && currentMoveIndex > 0) {
            currentMoveIndex--;
            game.undo();
            board.position(game.fen());
        } else if (direction === 'next' && currentMoveIndex < moveHistory.length - 1) {
            currentMoveIndex++;
            game.move(moveHistory[currentMoveIndex]);
            board.position(game.fen());
        }
    }

    // Copy PGN
    function copyPGN() {
        navigator.clipboard.writeText(game.pgn()).then(() => {
            showToast('PGN copied to clipboard', 'success');
        });
    }

    // Clear history
    function clearHistory() {
        const historyDiv = document.getElementById('moveHistory');
        historyDiv.innerHTML = '<div class="text-center py-8 text-slate-500">History cleared</div>';
        moveHistory = [];
        currentMoveIndex = -1;
    }

    // Fetch match details
    async function fetchMatchDetails() {
        try {
            const response = await fetch(API_URL);
            const data = await response.json();
            updateGameState(data);
            showToast('Game data refreshed', 'success');
        } catch (error) {
            showToast('Failed to refresh data', 'error');
        }
    }

    // Toast notification
    function showToast(message, type = 'info') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        const toastIcon = document.getElementById('toastIcon');
        
        toastMessage.textContent = message;
        
        let icon = '';
        let bgColor = 'bg-slate-800';
        
        switch(type) {
            case 'success':
                icon = '✓';
                bgColor = 'bg-green-900';
                break;
            case 'error':
                icon = '✕';
                bgColor = 'bg-red-900';
                break;
            case 'warning':
                icon = '⚠';
                bgColor = 'bg-yellow-900';
                break;
            default:
                icon = 'ℹ';
        }
        
        toastIcon.textContent = icon;
        toast.className = `fixed top-4 right-4 ${bgColor} border border-slate-700 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 z-50`;
        
        setTimeout(() => {
            toast.className = 'hidden';
        }, 3000);
    }

    function hideToast() {
        document.getElementById('toast').className = 'hidden';
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        initChessboard();
        connectWebSocket();
        
        // Event listeners
        document.getElementById('flipBoardBtn').addEventListener('click', flipBoard);
        document.getElementById('prevMove').addEventListener('click', () => navigateMoves('prev'));
        document.getElementById('nextMove').addEventListener('click', () => navigateMoves('next'));
        document.getElementById('copyPGN').addEventListener('click', copyPGN);
        document.getElementById('clearHistory').addEventListener('click', clearHistory);
        document.getElementById('reconnectBtn').addEventListener('click', connectWebSocket);
        
        // Initial fetch
        fetchMatchDetails();
        
        // Timer fallback
        timerInterval = setInterval(() => {
            if (game.turn() === 'w') {
                whiteTime = Math.max(0, whiteTime - 1);
                updateTimer('white', whiteTime);
            } else {
                blackTime = Math.max(0, blackTime - 1);
                updateTimer('black', blackTime);
            }
            
            if (whiteTime === 0 || blackTime === 0) {
                handleGameOver(whiteTime === 0 ? '0-1' : '1-0');
            }
        }, 1000);
    });
    
    // Cleanup
    window.addEventListener('beforeunload', () => {
        if (socket) socket.disconnect();
        if (timerInterval) clearInterval(timerInterval);
    });
</script>

<?= $this->endSection() ?>
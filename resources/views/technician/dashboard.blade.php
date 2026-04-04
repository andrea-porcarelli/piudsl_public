<!DOCTYPE html>
<html lang="it" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="maps-api-key" content="{{ $mapsApiKey }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>PiùDSL — Area Tecnico</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'display': ['Outfit', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        'brand': {
                            50:  '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        'accent': {
                            400: '#facc15',
                            500: '#eab308',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body { padding-bottom: env(safe-area-inset-bottom); }
        .tab-bar { padding-bottom: env(safe-area-inset-bottom); }
        .content-area { padding-bottom: calc(4.5rem + env(safe-area-inset-bottom)); }

        .skeleton { background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

        .tab-active { color: #0284c7; border-top: 2px solid #0284c7; }
        .tab-inactive { color: #94a3b8; border-top: 2px solid transparent; }
    </style>
</head>
<body class="font-display antialiased bg-gray-50 text-gray-900">

<!-- Fixed Header -->
<header class="fixed top-0 left-0 right-0 z-40 bg-brand-700 text-white shadow-lg" style="padding-top: env(safe-area-inset-top)">
    <div class="flex items-center justify-between px-4 h-14">
        <div class="flex items-center">
            <img src="/piudsl.png" alt="PiùDSL" class="h-8 w-auto">
        </div>

        <span class="text-sm font-medium text-brand-100 truncate max-w-[140px]">{{ $userName }}</span>

        <form method="POST" action="/auth/logout" id="logout-form">
            @csrf
            <button type="button" onclick="handleLogout()" class="p-2 rounded-lg hover:bg-white/10 transition-colors" aria-label="Logout">
                <i data-feather="log-out" class="w-5 h-5"></i>
            </button>
        </form>
    </div>
</header>

<!-- Session expired overlay -->
<div id="session-expired" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 max-w-sm w-full text-center shadow-xl">
        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-feather="clock" class="w-7 h-7 text-red-500"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Sessione scaduta</h3>
        <p class="text-gray-500 text-sm mb-4">Accedi nuovamente per continuare.</p>
        <a href="/" class="block bg-brand-600 text-white font-semibold py-3 rounded-xl text-sm">Torna al login</a>
    </div>
</div>

<!-- Report Modal -->
<div id="report-modal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-end justify-center p-4 pb-8">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
        <div class="flex items-center justify-between px-5 pt-5 pb-3 border-b border-gray-100">
            <div class="flex items-center space-x-2">
                <i data-feather="alert-triangle" class="w-4 h-4 text-orange-500"></i>
                <h3 class="text-base font-bold text-gray-900">Segnalazione al backoffice</h3>
            </div>
            <button onclick="closeReportModal()" class="p-1 text-gray-400 active:text-gray-600">
                <i data-feather="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="px-5 py-4 space-y-4">
            <div class="space-y-1">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wide">Data</label>
                <input type="date" id="report-date"
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50 focus:outline-none focus:border-brand-400">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wide">Nota</label>
                <textarea id="report-note" rows="4" placeholder="Descrivi la segnalazione…"
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 resize-none focus:outline-none focus:border-brand-400 bg-gray-50 placeholder-gray-400"></textarea>
            </div>
            <p id="report-err" class="hidden text-xs text-red-500 font-medium"></p>
            <button onclick="saveReport()" id="report-submit-btn"
                class="w-full text-sm font-semibold bg-orange-500 text-white py-3 rounded-xl active:bg-orange-600 disabled:opacity-50">Invia segnalazione</button>
        </div>
    </div>
</div>

<!-- Main Content -->
<main class="content-area pt-14">

    <!-- ===== AGENDA ===== -->
    <section id="section-agenda" class="px-4 py-4 space-y-3">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-base font-semibold text-gray-700">Agenda</h2>
            <div class="flex items-center space-x-3">
                <button onclick="openReportModal()" class="text-orange-500 text-sm font-medium flex items-center space-x-1">
                    <i data-feather="alert-triangle" class="w-3.5 h-3.5"></i>
                    <span>Segnala</span>
                </button>
                <button onclick="loadAgenda()" class="text-brand-600 text-sm font-medium flex items-center space-x-1">
                    <i data-feather="refresh-cw" class="w-3.5 h-3.5"></i>
                    <span>Aggiorna</span>
                </button>
            </div>
        </div>

        <!-- Navigatore data -->
        <div class="flex items-center bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <button onclick="shiftDate(-1)" class="px-4 py-2.5 text-gray-400 active:bg-gray-50 transition-colors">
                <i data-feather="chevron-left" class="w-4 h-4"></i>
            </button>
            <input type="date" id="agenda-date" onchange="onDateChange()"
                   class="flex-1 text-center text-sm font-semibold text-gray-800 focus:outline-none bg-transparent py-2.5 cursor-pointer">
            <button onclick="shiftDate(1)" class="px-4 py-2.5 text-gray-400 active:bg-gray-50 transition-colors">
                <i data-feather="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="flex justify-center -mt-1">
            <button id="today-btn" onclick="goToToday()" class="hidden text-xs text-brand-600 font-medium underline underline-offset-2">
                Torna ad oggi
            </button>
        </div>

        <div id="agenda-loading" class="space-y-3">
            <div class="skeleton h-28 rounded-2xl"></div>
            <div class="skeleton h-28 rounded-2xl"></div>
            <div class="skeleton h-28 rounded-2xl"></div>
        </div>
        <div id="agenda-error" class="hidden text-center py-10">
            <i data-feather="alert-circle" class="w-10 h-10 text-red-400 mx-auto mb-2"></i>
            <p class="text-gray-500 text-sm mb-3">Impossibile caricare l'agenda.</p>
            <button onclick="loadAgenda()" class="text-brand-600 text-sm font-medium">Riprova</button>
        </div>
        <div id="agenda-empty" class="hidden text-center py-10">
            <i data-feather="calendar" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
            <p class="text-gray-400 text-sm">Nessuna attività per questa data.</p>
        </div>
        <div id="agenda-list" class="hidden space-y-3"></div>
    </section>

    <!-- ===== FATTURE CARTACEE ===== -->
    <section id="section-invoices" class="hidden px-4 py-4 space-y-3">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-base font-semibold text-gray-700">Consegna Fatture cartacee</h2>
            <div class="flex items-center space-x-3">
                <button id="toggle-delivered-btn" onclick="toggleDelivered()"
                    class="text-xs text-gray-400 font-medium flex items-center space-x-1">
                    <i data-feather="eye" class="w-3.5 h-3.5"></i>
                    <span>Consegnate</span>
                </button>
                <button onclick="reloadInvoices()" class="text-brand-600 text-sm font-medium flex items-center space-x-1">
                    <i data-feather="refresh-cw" class="w-3.5 h-3.5"></i>
                    <span>Aggiorna</span>
                </button>
            </div>
        </div>

        <!-- Location search -->
        <div class="flex items-center space-x-2">
            <div class="relative flex-1">
                <i data-feather="map-pin" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"></i>
                <input id="invoice-address-input" type="text" placeholder="Indirizzo o posizione…"
                    class="w-full text-xs pl-7 pr-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-400 bg-gray-50">
            </div>
            <button id="invoice-gps-btn" onclick="detectLocation()"
                class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-xl bg-brand-600 text-white active:bg-brand-700 transition-colors"
                aria-label="Usa la mia posizione">
                <i data-feather="crosshair" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Month / Year selector -->
        <div class="flex items-center space-x-2">
            <select id="invoice-month" onchange="reloadInvoices()" class="flex-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:border-brand-400 bg-gray-50">
                <option value="1">Gennaio</option><option value="2">Febbraio</option>
                <option value="3">Marzo</option><option value="4">Aprile</option>
                <option value="5">Maggio</option><option value="6">Giugno</option>
                <option value="7">Luglio</option><option value="8">Agosto</option>
                <option value="9">Settembre</option><option value="10">Ottobre</option>
                <option value="11">Novembre</option><option value="12">Dicembre</option>
            </select>
            <select id="invoice-year" onchange="reloadInvoices()" class="w-24 text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:border-brand-400 bg-gray-50"></select>
        </div>

        <div id="invoices-loading" class="space-y-3">
            <div class="skeleton h-24 rounded-2xl"></div>
            <div class="skeleton h-24 rounded-2xl"></div>
            <div class="skeleton h-24 rounded-2xl"></div>
        </div>
        <div id="invoices-error" class="hidden text-center py-10">
            <i data-feather="alert-circle" class="w-10 h-10 text-red-400 mx-auto mb-2"></i>
            <p class="text-gray-500 text-sm mb-3">Impossibile caricare le fatture.</p>
            <button onclick="loadInvoices()" class="text-brand-600 text-sm font-medium">Riprova</button>
        </div>
        <div id="invoices-empty" class="hidden text-center py-10">
            <i data-feather="file-text" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
            <p class="text-gray-400 text-sm">Nessuna fattura cartacea per questo periodo.</p>
        </div>
        <div id="invoices-list" class="hidden space-y-3"></div>
    </section>


</main>

<!-- ===== ACTIVITY DETAIL SHEET ===== -->
<div id="activity-sheet" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/50" onclick="closeActivitySheet()"></div>
    <div id="activity-sheet-panel"
         class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl flex flex-col shadow-2xl"
         style="max-height:92vh; transform:translateY(100%); transition:transform 0.35s cubic-bezier(0.32,0.72,0,1)">
        <!-- Handle -->
        <div class="flex-shrink-0 flex justify-center py-3 cursor-pointer" onclick="closeActivitySheet()">
            <div class="w-10 h-1 rounded-full bg-gray-300"></div>
        </div>
        <!-- Header -->
        <div class="flex-shrink-0 flex items-center justify-between px-5 pb-3 border-b border-gray-100">
            <div id="sheet-header-badge" class="flex items-center space-x-2"></div>
            <button onclick="closeActivitySheet()" class="p-2 -mr-2 rounded-xl text-gray-400 active:bg-gray-100">
                <i data-feather="x" class="w-5 h-5"></i>
            </button>
        </div>
        <!-- Body -->
        <div class="flex-1 overflow-y-auto" style="padding-bottom: env(safe-area-inset-bottom)">
            <div id="sheet-loading" class="px-5 py-6 space-y-3">
                <div class="skeleton h-5 rounded-lg w-3/4"></div>
                <div class="skeleton h-4 rounded-lg w-1/2"></div>
                <div class="skeleton h-4 rounded-lg w-2/3"></div>
                <div class="skeleton h-24 rounded-xl w-full mt-2"></div>
                <div class="skeleton h-24 rounded-xl w-full"></div>
            </div>
            <div id="sheet-content" class="hidden px-5 pt-4 space-y-6" style="padding-bottom:2.5rem"></div>
        </div>
    </div>
</div>

<!-- Toast consegna -->
<div id="deliver-toast"
    class="hidden fixed top-[calc(env(safe-area-inset-top)+3.5rem+0.75rem)] left-4 right-4 z-50
           bg-green-600 text-white text-sm font-semibold rounded-2xl px-4 py-3
           flex items-center space-x-2 shadow-lg
           transition-all duration-300 opacity-0 translate-y-0">
    <i data-feather="check-circle" class="w-4 h-4 flex-shrink-0"></i>
    <span>Fattura segnata come consegnata</span>
</div>

<!-- Bottom Tab Bar -->
<nav class="tab-bar fixed bottom-0 left-0 right-0 z-40 bg-white grid grid-cols-2">
    <button id="tab-agenda"   onclick="switchTab('agenda')"   class="tab-active flex flex-col items-center justify-center pt-2 pb-1 space-y-0.5 transition-colors">
        <i data-feather="calendar" class="w-5 h-5"></i>
        <span class="text-[10px] font-semibold">Agenda</span>
    </button>
    <button id="tab-invoices" onclick="switchTab('invoices')" class="tab-inactive flex flex-col items-center justify-center pt-2 pb-1 space-y-0.5 transition-colors">
        <i data-feather="file-text" class="w-5 h-5"></i>
        <span class="text-[10px] font-semibold">Consegna Fatture</span>
    </button>
</nav>

<script>
feather.replace();

const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const CURRENT_USER_ID = {{ $userId ?? 'null' }};

// ── Loaded flags (lazy loading) ──────────────────────────────────────────────
const loaded = { agenda: false, invoices: false };

// ── Tickets state ─────────────────────────────────────────────────────────────
let allTickets = [];
let activeFilter = 'all';

// ── Calendar events state ─────────────────────────────────────────────────────
let allCalendarEvents = [];
let allActivities     = [];
let calendarFilter    = 'open';

// ── Session expired ──────────────────────────────────────────────────────────
function showSessionExpired() {
    document.getElementById('session-expired').classList.remove('hidden');
}

// ── Tab switching ─────────────────────────────────────────────────────────────
const tabs     = ['agenda', 'invoices'];
const sections = { agenda: 'section-agenda', invoices: 'section-invoices' };

function switchTab(name) {
    tabs.forEach(t => {
        document.getElementById('tab-' + t).className =
            (t === name ? 'tab-active' : 'tab-inactive') +
            ' flex flex-col items-center justify-center pt-2 pb-1 space-y-0.5 transition-colors';
        document.getElementById(sections[t]).classList.toggle('hidden', t !== name);
    });

    if (!loaded[name]) {
        if (name === 'agenda')   loadAgenda();
        if (name === 'invoices') loadInvoicesFirstTime();
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function showState(prefix, state) {
    ['loading', 'error', 'empty', 'list'].forEach(s => {
        const el = document.getElementById(prefix + '-' + s);
        if (el) el.classList.toggle('hidden', s !== state);
    });
}

function handleNavigation(event, appUrl, encodedCoords) {
    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent);
    if (!isIos) return; // Android e desktop seguono href normalmente

    // Su iOS: prova ad aprire Google Maps app, fallback su Apple Maps
    event.preventDefault();
    const dest = decodeURIComponent(encodedCoords);
    const fallback = `maps://?daddr=${encodeURIComponent(dest)}&dirflg=d`;
    const timeout = setTimeout(() => { window.location.href = fallback; }, 1500);
    window.location.href = appUrl;
    window.addEventListener('blur', () => clearTimeout(timeout), { once: true });
}

function handleWaze(event, wazeAppUrl) {
    event.preventDefault();
    // Prova ad aprire l'app Waze; se non è installata usa il fallback web
    const coords = wazeAppUrl.replace('waze://?ll=', '').replace('&navigate=yes', '');
    const fallback = `https://waze.com/ul?ll=${coords}&navigate=yes`;
    const timeout = setTimeout(() => { window.open(fallback, '_blank'); }, 1500);
    window.location.href = wazeAppUrl;
    window.addEventListener('blur', () => clearTimeout(timeout), { once: true });
}

function formatDate(dateStr, timeStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr + (timeStr ? 'T' + timeStr : ''));
    return d.toLocaleDateString('it-IT', { day: '2-digit', month: 'short' }) +
           (timeStr ? ' ' + timeStr.slice(0, 5) : '');
}

function levelBadge(level) {
    const map = { high: 'bg-red-100 text-red-700', normal: 'bg-blue-100 text-blue-700', low: 'bg-gray-100 text-gray-600' };
    const labels = { high: 'Alta', normal: 'Normale', low: 'Bassa' };
    return `<span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${map[level] ?? 'bg-gray-100 text-gray-600'}">${labels[level] ?? level}</span>`;
}

function statusBadge(status) {
    const map = {
        open:        'bg-sky-100 text-sky-700',
        in_progress: 'bg-yellow-100 text-yellow-700',
        suspended:   'bg-orange-100 text-orange-700',
        completed:   'bg-green-100 text-green-700',
        pending:     'bg-purple-100 text-purple-700',
        close:       'bg-gray-100 text-gray-500',
        done:        'bg-green-100 text-green-700',
    };
    const labels = {
        open: 'Aperto', in_progress: 'In corso', suspended: 'Sospeso',
        completed: 'Completato', pending: 'In attesa', close: 'Chiuso', done: 'Fatto',
    };
    return `<span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${map[status] ?? 'bg-gray-100 text-gray-500'}">${labels[status] ?? status}</span>`;
}

// ── Logout ────────────────────────────────────────────────────────────────────
async function handleLogout() {
    await fetch('/auth/logout', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    window.location.href = '/';
}

// ── AGENDA (calendario + attività + ticket) ───────────────────────────────────
function initAgendaDate() {
    document.getElementById('agenda-date').value = new Date().toISOString().slice(0, 10);
}

function shiftDate(delta) {
    const input = document.getElementById('agenda-date');
    const d = new Date(input.value);
    d.setDate(d.getDate() + delta);
    input.value = d.toISOString().slice(0, 10);
    onDateChange();
}

function goToToday() {
    document.getElementById('agenda-date').value = new Date().toISOString().slice(0, 10);
    onDateChange();
}

function onDateChange() {
    loaded.agenda = false;
    loadAgenda();
}

async function loadAgenda() {
    showState('agenda', 'loading');
    loaded.agenda = false;

    const date  = document.getElementById('agenda-date').value;
    const today = new Date().toISOString().slice(0, 10);
    document.getElementById('today-btn').classList.toggle('hidden', date === today);

    try {
        const [calRes, actRes, tickRes] = await Promise.all([
            fetch('/api/technician/calendar-events', { headers: { 'X-CSRF-TOKEN': CSRF } }),
            fetch('/api/technician/cart-activities',  { headers: { 'X-CSRF-TOKEN': CSRF } }),
            fetch('/api/technician/tickets',           { headers: { 'X-CSRF-TOKEN': CSRF } }),
        ]);

        if ([calRes, actRes, tickRes].some(r => r.status === 401)) { showSessionExpired(); return; }

        const [calJson, actJson, tickJson] = await Promise.all([calRes.json(), actRes.json(), tickRes.json()]);

        // Filtra eventi e attività per la data selezionata, salva globalmente
        allCalendarEvents = (calJson.data ?? []).filter(ev =>
            ev.event_type === 'segnalazione' ||
            ((ev.start_date ?? '') <= date && (ev.end_date ?? ev.start_date ?? '') >= date)
        );
        allActivities = (actJson.data ?? []).filter(act => act.event_at === date);
        allTickets    = tickJson.data ?? [];
        calendarFilter = 'open';

        if (!allCalendarEvents.length && !allActivities.length && !allTickets.length) {
            showState('agenda', 'empty');
            loaded.agenda = true;
            return;
        }

        renderAgendaList();
        showState('agenda', 'list');
        loaded.agenda = true;

    } catch (e) {
        showState('agenda', 'error');
    }
}

function getFilteredCalendarEvents() {
    if (calendarFilter === 'all') return allCalendarEvents;
    return allCalendarEvents.filter(ev =>
        ev.status === calendarFilter || ev.event_type === 'segnalazione'
    );
}

function renderAgendaList() {
    const filtered = getFilteredCalendarEvents();
    const combined = [
        ...filtered.map(ev   => ({ type: 'calendar', time: ev.start_time   || '00:00', data: ev })),
        ...allActivities.map(act => ({ type: 'activity', time: act.event_time || '00:00', data: act })),
    ].sort((a, b) => a.time > b.time ? 1 : -1);

    let html = renderCalendarFilterBar();
    html += combined.map(item =>
        item.type === 'calendar' ? renderCalendarCard(item.data) : renderActivityCard(item.data)
    ).join('');
    if (allTickets.length) html += renderTicketsBlock();

    document.getElementById('agenda-list').innerHTML = html;
    feather.replace();
}

function renderCalendarFilterBar() {
    return `
    <div class="flex space-x-2 overflow-x-auto pb-1">
        <button onclick="filterCalendarEvents('open')" id="cal-filter-open"
            class="cal-filter flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full ${calendarFilter === 'open' ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600'}">Aperti</button>
        <button onclick="filterCalendarEvents('all')" id="cal-filter-all"
            class="cal-filter flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full ${calendarFilter === 'all' ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600'}">Tutti</button>
    </div>`;
}

function filterCalendarEvents(status) {
    calendarFilter = status;
    renderAgendaList();
}

function renderCalendarCard(ev) {
    const isSegnalazione = ev.event_type === 'segnalazione';
    const isMine = isSegnalazione && ev.technician_id === CURRENT_USER_ID;
    const color = ev.color || (isSegnalazione ? '#f97316' : '#0284c7');
    const histories = (ev.histories ?? []).map(h =>
        `<li class="text-xs text-gray-500 border-l-2 border-gray-200 pl-2 py-0.5">${h.note} <span class="text-gray-400 text-[10px]">${formatDate(h.created_at)}</span></li>`
    ).join('');
    const typeLabel = isSegnalazione
        ? `<span class="text-[10px] font-bold uppercase tracking-wide text-orange-400">Segnalazione</span>`
        : `<span class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Evento calendario</span>`;
    const mineBadge = isMine
        ? `<span class="inline-flex items-center gap-0.5 text-[10px] font-semibold bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full"><i data-feather="user" class="w-2.5 h-2.5"></i> La tua segnalazione</span>`
        : '';
    const borderClass = isSegnalazione ? 'border-orange-100' : 'border-gray-100';
    return `
    <div class="bg-white rounded-2xl shadow-sm border ${borderClass} overflow-hidden">
        <div class="h-1" style="background:${color}"></div>
        <div class="px-4 pt-2 pb-0 flex items-center justify-between">
            ${typeLabel}
            ${mineBadge}
        </div>
        <div class="p-4 pt-2">
            <div class="flex items-start justify-between gap-2 mb-2">
                <span class="font-semibold text-gray-900 text-sm leading-tight">${ev.title}</span>
                ${statusBadge(ev.status)}
            </div>
            <div class="flex items-center text-xs text-gray-500 space-x-1 mb-1">
                <i data-feather="clock" class="w-3 h-3"></i>
                <span>${formatDate(ev.start_date, ev.start_time)} → ${formatDate(ev.end_date, ev.end_time)}</span>
            </div>
            ${ev.customer ? `<div class="flex items-center text-xs text-gray-500 space-x-1 mb-2"><i data-feather="user" class="w-3 h-3"></i><span>${ev.customer}</span></div>` : ''}
            ${ev.description ? `<p class="text-xs text-gray-400 mb-2">${ev.description}</p>` : ''}
            ${histories ? `
            <details class="mt-1">
                <summary class="text-xs text-brand-600 cursor-pointer font-medium">Note (${ev.histories.length})</summary>
                <ul class="mt-2 space-y-1">${histories}</ul>
            </details>` : ''}
            <button onclick="openActivityDetail('calendar', ${ev.id})"
                class="mt-3 w-full flex items-center justify-center space-x-1.5 text-xs text-brand-600 font-semibold py-2 border border-brand-200 rounded-xl active:bg-brand-50 transition-colors">
                <i data-feather="edit-2" class="w-3.5 h-3.5"></i>
                <span>Gestisci</span>
            </button>
        </div>
    </div>`;
}

function renderActivityCard(act) {
    const mapsUrl = act.coordinates
        ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(act.coordinates)}`
        : `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(act.full_address ?? '')}`;
    return `
    <div class="bg-white rounded-2xl shadow-sm border border-amber-100 overflow-hidden">
        <div class="h-1 bg-amber-400"></div>
        <div class="px-4 pt-2 pb-0">
            <span class="text-[10px] font-bold uppercase tracking-wide text-amber-500">Attività</span>
        </div>
        <div class="p-4 pt-2">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div>
                    <span class="font-semibold text-gray-900 text-sm">${act.customer}</span>
                    ${act.is_first ? '<span class="ml-2 text-[10px] font-bold px-2 py-0.5 rounded-full bg-accent-400/20 text-yellow-700">Prima installazione</span>' : ''}
                </div>
                ${statusBadge(act.status)}
            </div>
            <a href="${mapsUrl}" target="_blank" rel="noopener" class="flex items-start space-x-1.5 text-xs text-brand-600 mb-2 active:opacity-70">
                <i data-feather="map-pin" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5"></i>
                <span class="underline underline-offset-2">${act.full_address ?? '—'}</span>
            </a>
            <div class="flex items-center text-xs text-gray-500 space-x-1">
                <i data-feather="clock" class="w-3 h-3"></i>
                <span>${act.event_time ? act.event_time.slice(0, 5) : '—'}</span>
            </div>
            ${act.note ? `<p class="text-xs text-gray-400 mt-2">${act.note}</p>` : ''}
            <button onclick="openActivityDetail('activity', ${act.id})"
                class="mt-3 w-full flex items-center justify-center space-x-1.5 text-xs text-amber-600 font-semibold py-2 border border-amber-200 rounded-xl active:bg-amber-50 transition-colors">
                <i data-feather="edit-2" class="w-3.5 h-3.5"></i>
                <span>Gestisci</span>
            </button>
        </div>
    </div>`;
}

function renderTicketsBlock() {
    return `
    <div class="mt-2">
        <div class="flex items-center space-x-1.5 mb-2 mt-4 px-1">
            <i data-feather="message-square" class="w-4 h-4 text-purple-400"></i>
            <h3 class="text-sm font-semibold text-gray-600">Ticket</h3>
        </div>
        <div class="flex space-x-2 overflow-x-auto pb-1">
            <button onclick="filterAgendaTickets('all')"     id="filter-all"     class="ticket-filter flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full ${activeFilter === 'all'     ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600'}">Tutti</button>
            <button onclick="filterAgendaTickets('open')"    id="filter-open"    class="ticket-filter flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full ${activeFilter === 'open'    ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600'}">Aperti</button>
            <button onclick="filterAgendaTickets('pending')" id="filter-pending" class="ticket-filter flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full ${activeFilter === 'pending' ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600'}">In attesa</button>
            <button onclick="filterAgendaTickets('close')"   id="filter-close"   class="ticket-filter flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full ${activeFilter === 'close'   ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600'}">Chiusi</button>
        </div>
        <div id="agenda-tickets-cards" class="space-y-3 mt-2">${renderTicketCards()}</div>
    </div>`;
}

function renderTicketCards() {
    const filtered = activeFilter === 'all' ? allTickets : allTickets.filter(t => t.ticket_status === activeFilter);
    if (!filtered.length) {
        return `<div class="text-center py-6">
            <i data-feather="inbox" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
            <p class="text-gray-400 text-sm">Nessun ticket trovato.</p>
        </div>`;
    }
    return filtered.map(t => `
        <div class="bg-white rounded-2xl shadow-sm border border-purple-50 p-4" id="ticket-${t.id}">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div class="flex flex-wrap gap-1.5">
                    ${levelBadge(t.ticket_level)}
                    ${statusBadge(t.ticket_status)}
                </div>
                <span class="text-[10px] text-gray-400 flex-shrink-0">#${t.id}</span>
            </div>
            <div class="flex items-center text-xs text-gray-600 space-x-1 mb-1">
                <i data-feather="user" class="w-3 h-3"></i>
                <span>${t.customer}</span>
                ${t.messages_count ? `<span class="ml-2 text-gray-400">· ${t.messages_count} msg</span>` : ''}
            </div>
            <div class="text-[10px] text-gray-400 mb-3">${formatDate(t.updated_at)}</div>
            <div class="flex items-center space-x-2">
                <select id="status-select-${t.id}" class="flex-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:border-brand-400 bg-gray-50">
                    <option value="open"    ${t.ticket_status === 'open'    ? 'selected' : ''}>Aperto</option>
                    <option value="pending" ${t.ticket_status === 'pending' ? 'selected' : ''}>In attesa</option>
                    <option value="close"   ${t.ticket_status === 'close'   ? 'selected' : ''}>Chiuso</option>
                </select>
                <button onclick="updateTicketStatus(${t.id})" class="text-xs bg-brand-600 hover:bg-brand-700 text-white font-semibold px-3 py-1.5 rounded-lg transition-colors disabled:opacity-50" id="status-btn-${t.id}">
                    Salva
                </button>
            </div>
            <button onclick="openActivityDetail('ticket', ${t.id})"
                class="mt-2 w-full flex items-center justify-center space-x-1.5 text-xs text-purple-600 font-semibold py-2 border border-purple-200 rounded-xl active:bg-purple-50 transition-colors">
                <i data-feather="edit-2" class="w-3.5 h-3.5"></i>
                <span>Gestisci</span>
            </button>
        </div>`
    ).join('');
}

function filterAgendaTickets(status) {
    activeFilter = status;
    document.querySelectorAll('.ticket-filter').forEach(btn => {
        btn.className = 'ticket-filter flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full ' +
            (btn.id === 'filter-' + status ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600');
    });
    const container = document.getElementById('agenda-tickets-cards');
    if (container) { container.innerHTML = renderTicketCards(); feather.replace(); }
}

// ── FATTURE CARTACEE ──────────────────────────────────────────────────────────
function toggleDelivered() {
    showDelivered = !showDelivered;
    const btn = document.getElementById('toggle-delivered-btn');
    btn.className = `text-xs font-medium flex items-center space-x-1 ${showDelivered ? 'text-brand-600' : 'text-gray-400'}`;
    reloadInvoices();
}

let toastTimer = null;
function showDeliverToast() {
    const toast = document.getElementById('deliver-toast');
    toast.classList.remove('hidden');
    requestAnimationFrame(() => {
        toast.classList.remove('opacity-0');
        toast.classList.add('opacity-100');
    });
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.classList.remove('opacity-100');
        toast.classList.add('opacity-0');
        setTimeout(() => toast.classList.add('hidden'), 300);
    }, 2200);
    feather.replace();
}

let invoiceCoords  = null;  // { lat, lng } — impostato da GPS o Autocomplete
let allInvoices    = [];   // cache per lookup in markDelivered()
let showDelivered  = false; // toggle visibilità card consegnate

(function initInvoiceSelectors() {
    const now  = new Date();
    const mSel = document.getElementById('invoice-month');
    const ySel = document.getElementById('invoice-year');

    mSel.value = now.getMonth() + 1;

    const currentYear = now.getFullYear();
    for (let y = currentYear; y >= currentYear - 3; y--) {
        const opt = document.createElement('option');
        opt.value = y; opt.textContent = y;
        ySel.appendChild(opt);
    }
})();

// Rilevamento GPS, restituisce una Promise che si risolve sempre (fallisce in silenzio)
function getLocationOrSkip() {
    return new Promise(resolve => {
        if (!navigator.geolocation) { resolve(); return; }
        navigator.geolocation.getCurrentPosition(
            pos => {
                invoiceCoords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                document.getElementById('invoice-address-input').value =
                    `${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)}`;
                resolve();
            },
            () => resolve(),
            { enableHighAccuracy: true, timeout: 8000 }
        );
    });
}

// Chiamato dal pulsante GPS
async function detectLocation() {
    const btn   = document.getElementById('invoice-gps-btn');
    const input = document.getElementById('invoice-address-input');
    btn.disabled = true;
    input.value  = '';
    input.placeholder = 'Rilevamento…';
    invoiceCoords = null;

    await getLocationOrSkip();

    btn.disabled = false;
    input.placeholder = 'Indirizzo o posizione…';

    if (invoiceCoords) {
        loaded.invoices = false;
        await loadInvoices();
    }
}

// Prima apertura del tab: tenta GPS poi carica
async function loadInvoicesFirstTime() {
    showState('invoices', 'loading');
    await getLocationOrSkip();
    await loadInvoices();
}

// Ricarica senza reset coordinate (cambio mese/anno o pulsante Aggiorna)
function reloadInvoices() {
    loaded.invoices = false;
    loadInvoices();
}

async function loadInvoices() {
    showState('invoices', 'loading');
    loaded.invoices = false;

    const month = document.getElementById('invoice-month').value;
    const year  = document.getElementById('invoice-year').value;

    let url = `/api/technician/invoices/paper?month=${month}&year=${year}`;
    if (invoiceCoords)  url += `&lat=${invoiceCoords.lat}&lng=${invoiceCoords.lng}`;
    if (showDelivered)  url += `&include_delivered=1`;

    try {
        const res  = await fetch(url, { headers: { 'X-CSRF-TOKEN': CSRF } });
        if (res.status === 401) { showSessionExpired(); return; }

        const json = await res.json();
        const data = json.data ?? [];

        if (!data.length) { showState('invoices', 'empty'); loaded.invoices = true; return; }

        allInvoices = data;

        const list = document.getElementById('invoices-list');
        list.innerHTML = data.map((inv, i) => {
            const isDelivered = !!inv.delivered_at;

            const distBadge = inv.distance_km != null
                ? `<span class="flex items-center space-x-0.5 text-[10px] text-brand-600 font-semibold">
                       <i data-feather="navigation" class="w-3 h-3"></i>
                       <span>${inv.distance_km < 1 ? (inv.distance_km * 1000).toFixed(0) + ' m' : inv.distance_km.toFixed(1) + ' km'}</span>
                   </span>`
                : '';

            const prevCoords = i === 0
                ? (invoiceCoords ? `${invoiceCoords.lat},${invoiceCoords.lng}` : null)
                : (data[i - 1].coordinates || null);

            const wazeUrl = (() => {
                if (!inv.coordinates) return null;
                const [lat, lng] = inv.coordinates.split(',').map(s => s.trim());
                if (!lat || !lng) return null;
                // waze:// apre direttamente l'app con navigazione immediata.
                // Fallback https:// per chi non ha Waze installato (gestito da handleWaze).
                return `waze://?ll=${lat},${lng}&navigate=yes`;
            })();

            const directionsUrl = (() => {
                if (!inv.coordinates) return null;
                const dest = encodeURIComponent(inv.coordinates);
                const origin = prevCoords ? encodeURIComponent(prevCoords) : null;
                // iOS: deep link all'app Google Maps
                const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
                if (isIos) {
                    return origin
                        ? `comgooglemaps://?saddr=${origin}&daddr=${dest}&directionsmode=driving`
                        : `comgooglemaps://?daddr=${dest}&directionsmode=driving`;
                }
                // Android: intent:// bypassa l'App Link e lancia direttamente
                // Google Maps in modalità navigazione, anche sui tablet
                const isMobileOrTablet = navigator.maxTouchPoints > 0;
                if (isMobileOrTablet) {
                    const navParams = origin
                        ? `maps?saddr=${origin}&daddr=${dest}&dirflg=d`
                        : `maps?daddr=${dest}&dirflg=d`;
                    return `intent://maps.google.com/${navParams}` +
                           `#Intent;scheme=https;package=com.google.android.apps.maps;end`;
                }
                // Desktop
                return origin
                    ? `https://maps.google.com/maps?saddr=${origin}&daddr=${dest}&dirflg=d`
                    : `https://maps.google.com/maps?daddr=${dest}&dirflg=d`;
            })();

            const deliveredInfo = isDelivered
                ? `<div class="mt-3 flex items-center space-x-1.5 text-xs text-green-600">
                       <i data-feather="check-circle" class="w-3.5 h-3.5 flex-shrink-0"></i>
                       <span>Consegnata il ${formatDate(inv.delivered_at)}${inv.delivered_by ? ' · ' + inv.delivered_by : ''}</span>
                   </div>`
                : `<div class="mt-3 space-y-2">
                       <textarea
                           id="deliver-notes-${inv.id}"
                           rows="2"
                           placeholder="Note (facoltative)…"
                           class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2 resize-none focus:outline-none focus:border-brand-400 bg-gray-50 placeholder-gray-400"></textarea>
                       <button
                           id="deliver-btn-${inv.id}"
                           data-invoice-idx="${i}"
                           onclick="markDelivered(this)"
                           class="w-full flex items-center justify-center space-x-1.5 bg-green-600 active:bg-green-700 text-white text-xs font-semibold py-2.5 rounded-xl transition-colors">
                           <i data-feather="check-circle" class="w-3.5 h-3.5"></i>
                           <span>Segna come consegnata</span>
                       </button>
                   </div>`;

            return `
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border ${isDelivered ? 'border-green-100' : 'border-gray-100'}">
                ${invoiceCoords ? `<div class="bg-gray-50 border-b ${isDelivered ? 'border-green-100' : 'border-gray-100'} px-4 py-1 flex items-center justify-between">
                    <span class="text-[10px] text-gray-400 font-medium">#${i + 1} nel percorso</span>
                    ${distBadge}
                </div>` : ''}
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3 mb-1">
                        <p class="text-base font-bold text-gray-900 leading-snug">${inv.customer}</p>
                        ${isDelivered
                            ? `<span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Consegnata</span>`
                            : `<span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">Da consegnare</span>`}
                    </div>
                    <p class="text-xs text-gray-400 mb-3">${inv.invoice_code} · ${inv.type_label}</p>

                    ${(inv.full_address || inv.coordinates) ? `
                    <div class="flex items-start space-x-1.5 text-xs text-gray-500 mb-3">
                        <i data-feather="map-pin" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-gray-400"></i>
                        <span>${inv.full_address ?? inv.coordinates}</span>
                    </div>` : ''}
                    ${(directionsUrl || wazeUrl) ? `
                    <div class="mb-3 flex items-center gap-4">
                        ${directionsUrl ? `
                        <a href="${directionsUrl}" target="_blank" rel="noopener"
                            onclick="handleNavigation(event, '${directionsUrl}', '${encodeURIComponent(inv.coordinates)}')"
                            class="flex items-center space-x-1.5 text-xs text-brand-600 font-medium active:opacity-70">
                            <i data-feather="navigation" class="w-3.5 h-3.5 flex-shrink-0"></i>
                            <span class="underline underline-offset-2">Google Maps</span>
                        </a>` : ''}
                        ${wazeUrl ? `
                        <a href="${wazeUrl}" rel="noopener"
                            onclick="handleWaze(event, '${wazeUrl}')"
                            class="flex items-center space-x-1.5 text-xs text-blue-500 font-medium active:opacity-70">
                            <i data-feather="navigation" class="w-3.5 h-3.5 flex-shrink-0"></i>
                            <span class="underline underline-offset-2">Waze</span>
                        </a>` : ''}
                    </div>` : ''}


                    ${inv.notes ? `<p class="text-xs text-gray-400 mt-2 italic">${inv.notes}</p>` : ''}

                    ${deliveredInfo}
                </div>
            </div>`;
        }).join('');

        showState('invoices', 'list');
        loaded.invoices = true;
        feather.replace();

    } catch (e) {
        showState('invoices', 'error');
    }
}

// Google Places Autocomplete (callback caricato dallo script Maps)
function onGoogleMapsLoaded() {
    const input = document.getElementById('invoice-address-input');
    if (!input || !window.google) return;

    const autocomplete = new google.maps.places.Autocomplete(input, {
        types: ['geocode'],
        componentRestrictions: { country: 'it' },
        fields: ['geometry', 'formatted_address'],
    });

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.geometry?.location) return;
        invoiceCoords = { lat: place.geometry.location.lat(), lng: place.geometry.location.lng() };
        loaded.invoices = false;
        loadInvoices();
    });
}

// ── Segna fattura come consegnata ─────────────────────────────────────────────
async function markDelivered(btn) {
    const idx = parseInt(btn.dataset.invoiceIdx, 10);
    const inv = allInvoices[idx];
    if (!inv) return;

    btn.disabled = true;
    btn.querySelector('span').textContent = 'Invio…';

    const notesEl = document.getElementById(`deliver-notes-${inv.id}`);
    const notes   = notesEl ? notesEl.value.trim() : '';

    try {
        const res = await fetch(`/api/technician/invoices/paper/${inv.id}/deliver`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(notes ? { notes } : {}),
        });

        if (res.status === 401) { showSessionExpired(); return; }

        if (!res.ok) {
            btn.querySelector('span').textContent = 'Errore, riprova';
            setTimeout(() => {
                btn.querySelector('span').textContent = 'Segna come consegnata';
                btn.disabled = false;
            }, 2000);
            return;
        }

        // Aggiorna le coordinate di partenza alla posizione di questa fattura
        if (inv.coordinates) {
            const [lat, lng] = inv.coordinates.split(',').map(Number);
            invoiceCoords = { lat, lng };
            document.getElementById('invoice-address-input').value =
                inv.full_address || inv.coordinates;
        }

        // Feedback positivo, poi ricarica
        showDeliverToast();
        await new Promise(r => setTimeout(r, 900));
        loaded.invoices = false;
        await loadInvoices();

    } catch (e) {
        btn.querySelector('span').textContent = 'Errore, riprova';
        setTimeout(() => {
            btn.querySelector('span').textContent = 'Segna come consegnata';
            btn.disabled = false;
        }, 2000);
    }
}


async function updateTicketStatus(id) {
    const select = document.getElementById('status-select-' + id);
    const btn    = document.getElementById('status-btn-' + id);
    const newStatus = select.value;

    btn.disabled = true;
    btn.textContent = '...';

    try {
        const res = await fetch('/api/technician/tickets/' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ticket_status: newStatus }),
        });

        if (res.status === 401) { showSessionExpired(); return; }

        if (res.ok) {
            const ticket = allTickets.find(t => t.id === id);
            if (ticket) ticket.ticket_status = newStatus;

            btn.textContent = '✓';
            btn.className = btn.className.replace('bg-brand-600 hover:bg-brand-700', 'bg-green-500');
            setTimeout(() => {
                btn.textContent = 'Salva';
                btn.className = btn.className.replace('bg-green-500', 'bg-brand-600 hover:bg-brand-700');
                btn.disabled = false;
                const container = document.getElementById('agenda-tickets-cards');
                if (container) { container.innerHTML = renderTicketCards(); feather.replace(); }
            }, 1500);
        } else {
            btn.textContent = 'Errore';
            setTimeout(() => { btn.textContent = 'Salva'; btn.disabled = false; }, 1500);
        }

    } catch (e) {
        btn.textContent = 'Errore';
        setTimeout(() => { btn.textContent = 'Salva'; btn.disabled = false; }, 1500);
    }
}

// ── ACTIVITY DETAIL SHEET ────────────────────────────────────────────────────

let _sheetType = null;
let _sheetId   = null;
let _sheetData = null;
let _availableProducts = null;

const SHEET_STATUS_OPTIONS = {
    calendar: [
        { value: 'open',        label: 'Aperto' },
        { value: 'in_progress', label: 'In corso' },
        { value: 'suspended',   label: 'Sospeso' },
        { value: 'completed',   label: 'Completato' },
        { value: 'close',       label: 'Chiuso' },
    ],
    ticket: [
        { value: 'open',    label: 'Aperto' },
        { value: 'pending', label: 'In attesa' },
        { value: 'close',   label: 'Chiuso' },
    ],
    activity: [
        { value: 'open',      label: 'Aperto' },
        { value: 'suspended', label: 'Sospeso' },
        { value: 'completed', label: 'Completato' },
    ],
};

async function openActivityDetail(type, id) {
    _sheetType = type;
    _sheetId   = id;
    _sheetData = null;

    // Reset sheet UI
    document.getElementById('sheet-loading').classList.remove('hidden');
    document.getElementById('sheet-content').classList.add('hidden');
    document.getElementById('sheet-header-badge').innerHTML = _sheetTypeBadgeHtml(type);

    const sheet = document.getElementById('activity-sheet');
    const panel = document.getElementById('activity-sheet-panel');
    sheet.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => requestAnimationFrame(() => {
        panel.style.transform = 'translateY(0)';
    }));
    feather.replace();

    try {
        const paths = {
            calendar: `/api/technician/calendar-events/${id}`,
            ticket:   `/api/technician/tickets/${id}`,
            activity: `/api/technician/cart-activities/${id}`,
        };
        const res = await fetch(paths[type], { headers: { 'X-CSRF-TOKEN': CSRF } });
        if (res.status === 401) { showSessionExpired(); closeActivitySheet(); return; }

        const json = await res.json();
        _sheetData = json.data ?? json;

        if (type === 'activity' && _availableProducts === null) {
            await _loadAvailableProducts();
        }

        _renderSheetContent();
    } catch (e) {
        document.getElementById('sheet-loading').innerHTML = `
            <div class="text-center py-10">
                <i data-feather="alert-circle" class="w-10 h-10 text-red-400 mx-auto mb-2"></i>
                <p class="text-gray-500 text-sm mb-2">Impossibile caricare i dettagli.</p>
                <button onclick="openActivityDetail('${type}',${id})" class="text-brand-600 text-sm font-medium">Riprova</button>
            </div>`;
        feather.replace();
    }
}

function closeActivitySheet() {
    const panel = document.getElementById('activity-sheet-panel');
    panel.style.transform = 'translateY(100%)';
    setTimeout(() => {
        document.getElementById('activity-sheet').classList.add('hidden');
        document.body.style.overflow = '';
    }, 370);
}

function _sheetTypeBadgeHtml(type) {
    const cfg = {
        calendar: { label: 'Evento Calendario', cls: 'text-brand-700 bg-brand-50',   icon: 'calendar' },
        ticket:   { label: 'Ticket',            cls: 'text-purple-700 bg-purple-50', icon: 'message-square' },
        activity: { label: 'Installazione',      cls: 'text-amber-700 bg-amber-50',   icon: 'tool' },
    }[type] ?? { label: type, cls: 'text-gray-700 bg-gray-100', icon: 'info' };
    return `<span class="flex items-center space-x-1.5 text-xs font-bold px-3 py-1.5 rounded-full ${cfg.cls}">
        <i data-feather="${cfg.icon}" class="w-3.5 h-3.5"></i><span>${cfg.label}</span>
    </span>`;
}

function _renderSheetContent() {
    document.getElementById('sheet-loading').classList.add('hidden');
    const el = document.getElementById('sheet-content');
    el.classList.remove('hidden');
    if (_sheetType === 'calendar') el.innerHTML = _buildCalendarContent(_sheetData);
    if (_sheetType === 'ticket')   el.innerHTML = _buildTicketContent(_sheetData);
    if (_sheetType === 'activity') el.innerHTML = _buildActivityContent(_sheetData);
    feather.replace();
}

// ── Escape helper ─────────────────────────────────────────────────────────────
function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Section builders ──────────────────────────────────────────────────────────
function _infoRow(icon, text) {
    if (!text) return '';
    return `<div class="flex items-start space-x-2 text-sm text-gray-600">
        <i data-feather="${icon}" class="w-4 h-4 flex-shrink-0 mt-0.5 text-gray-400"></i>
        <span>${esc(text)}</span></div>`;
}

function _buildFormSection(currentStatus, notes = []) {
    const opts = (SHEET_STATUS_OPTIONS[_sheetType] ?? []).map(o =>
        `<option value="${o.value}"${currentStatus === o.value ? ' selected' : ''}>${o.label}</option>`
    ).join('');
    const list = notes.map(n => `
        <div class="border-l-2 border-brand-200 pl-3">
            <p class="text-sm text-gray-700 leading-snug">${esc(n.body ?? n.note ?? '')}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">${n.created_by ? esc(n.created_by) + ' · ' : ''}${formatDate(n.created_at)}</p>
        </div>`).join('');
    return `<div class="bg-gray-50 rounded-2xl p-4 space-y-4">
        <div class="space-y-2">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Stato</p>
            <select id="sheet-status" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-brand-400">${opts}</select>
        </div>
        <div class="space-y-3">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Note</p>
            ${list ? `<div class="space-y-3">${list}</div>` : '<p class="text-xs text-gray-400">Nessuna nota.</p>'}
            <textarea id="sheet-note-input" rows="3" placeholder="Aggiungi una nota (opzionale)…"
                class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 resize-none focus:outline-none focus:border-brand-400 bg-white placeholder-gray-400"></textarea>
        </div>
        <button onclick="saveSheetChanges()" id="sheet-save-btn"
            class="w-full text-sm font-semibold bg-brand-600 text-white py-3 rounded-xl active:bg-brand-700 disabled:opacity-50">Salva</button>
        <p id="sheet-save-fb" class="hidden text-xs text-green-600 font-medium text-center">Salvato.</p>
    </div>`;
}

function openReportModal() {
    document.getElementById('report-date').value = new Date().toISOString().slice(0, 10);
    document.getElementById('report-note').value = '';
    document.getElementById('report-err').classList.add('hidden');
    document.getElementById('report-note').classList.remove('border-red-400');
    document.getElementById('report-submit-btn').disabled = false;
    document.getElementById('report-submit-btn').textContent = 'Invia segnalazione';
    document.getElementById('report-modal').classList.remove('hidden');
    feather.replace();
}

function closeReportModal() {
    document.getElementById('report-modal').classList.add('hidden');
}

async function saveReport() {
    const date  = document.getElementById('report-date').value;
    const note  = document.getElementById('report-note').value.trim();
    const btn   = document.getElementById('report-submit-btn');
    const errEl = document.getElementById('report-err');

    if (!note) {
        document.getElementById('report-note').classList.add('border-red-400');
        document.getElementById('report-note').focus();
        return;
    }
    document.getElementById('report-note').classList.remove('border-red-400');

    btn.disabled = true; btn.textContent = '…';
    errEl.classList.add('hidden');

    try {
        const res = await fetch('/api/technician/reports', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ date, note }),
        });
        if (res.status === 401) { showSessionExpired(); return; }
        if (res.ok) {
            closeReportModal();
        } else {
            const json = await res.json();
            errEl.textContent = json.message ?? 'Errore durante l\'invio.';
            errEl.classList.remove('hidden');
            btn.disabled = false; btn.textContent = 'Invia segnalazione';
        }
    } catch (_) {
        errEl.textContent = 'Errore di rete. Riprova.';
        errEl.classList.remove('hidden');
        btn.disabled = false; btn.textContent = 'Invia segnalazione';
    }
}

function _buildAttachmentsSection(attachments = []) {
    const thumbs = attachments.map(a =>
        `<a href="${a.url}" target="_blank" rel="noopener" class="block aspect-square rounded-xl overflow-hidden bg-gray-100 active:opacity-80">
            <img src="${a.url}" alt="" class="w-full h-full object-cover" loading="lazy">
        </a>`).join('');
    return `<div class="space-y-3" id="sheet-attachments-wrap">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Immagini</p>
        ${attachments.length
            ? `<div class="grid grid-cols-3 gap-2">${thumbs}</div>`
            : '<p class="text-xs text-gray-400">Nessuna immagine.</p>'}
        <label class="flex items-center justify-center space-x-2 w-full py-3 border-2 border-dashed border-gray-200 rounded-xl text-sm text-gray-500 active:bg-gray-50 cursor-pointer">
            <i data-feather="camera" class="w-4 h-4"></i>
            <span id="sheet-upload-txt">Carica immagini</span>
            <input type="file" accept="image/*" multiple class="hidden" onchange="uploadSheetImages(this)">
        </label>
        <p id="sheet-upload-fb" class="hidden text-xs text-green-600 font-medium">Immagini caricate.</p>
    </div>`;
}

// ── Calendar ──────────────────────────────────────────────────────────────────
function _buildCalendarContent(d) {
    const histories = (d.histories ?? []).map(h =>
        `<div class="border-l-2 border-gray-200 pl-3">
            <p class="text-xs text-gray-500">${esc(h.note ?? '')}</p>
            <p class="text-[10px] text-gray-400">${formatDate(h.created_at)}</p>
        </div>`).join('');
    return `
    <div class="space-y-1">
        <h2 class="text-lg font-bold text-gray-900 leading-snug">${esc(d.title ?? '')}</h2>
        ${_infoRow('user', d.customer)}
        ${_infoRow('clock', formatDate(d.start_date, d.start_time) + ' → ' + formatDate(d.end_date, d.end_time))}
        ${d.description ? `<p class="text-sm text-gray-500 pt-1">${esc(d.description)}</p>` : ''}
    </div>
    ${_buildFormSection(d.status, d.notes ?? [])}
    ${_buildAttachmentsSection(d.attachments ?? [])}
    ${histories ? `<div class="space-y-2">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Storico</p>
        <div class="space-y-2">${histories}</div>
    </div>` : ''}`;
}

// ── Ticket ────────────────────────────────────────────────────────────────────
function _buildTicketContent(d) {
    return `
    <div class="space-y-1">
        <div class="flex items-center space-x-2 mb-1">${levelBadge(d.ticket_level)}<span class="text-[10px] text-gray-400">#${d.id}</span></div>
        <h2 class="text-base font-bold text-gray-900">${esc(d.customer ?? '')}</h2>
        ${_infoRow('clock', formatDate(d.updated_at))}
        ${d.messages_count ? `<p class="text-xs text-gray-400">${d.messages_count} messaggi</p>` : ''}
    </div>
    ${_buildFormSection(d.ticket_status, d.notes ?? [])}
    ${_buildAttachmentsSection(d.attachments ?? [])}`;
}

// ── Cart Activity ─────────────────────────────────────────────────────────────
function _buildActivityContent(d) {
    const offer = d.offer;
    const offerHtml = offer ? `
    <div class="bg-brand-50 border border-brand-100 rounded-2xl p-4 space-y-1">
        <p class="text-xs font-bold text-brand-600 uppercase tracking-wide">Offerta acquistata</p>
        <p class="text-base font-bold text-gray-900">${esc(offer.name ?? '')}</p>
        ${offer.description ? `<p class="text-xs text-gray-500">${esc(offer.description)}</p>` : ''}
        <p class="text-sm font-semibold text-brand-700">€ ${parseFloat(offer.price ?? 0).toFixed(2)} / mese</p>
    </div>` : '';

    return `
    <div class="space-y-1">
        <h2 class="text-lg font-bold text-gray-900">${esc(d.customer ?? '')}</h2>
        ${_infoRow('map-pin', d.full_address)}
        ${_infoRow('clock', (d.event_at ? formatDate(d.event_at) : '') + (d.event_time ? ' ' + d.event_time.slice(0,5) : ''))}
        ${d.is_first ? '<span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-accent-400/20 text-yellow-700 mt-1">Prima installazione</span>' : ''}
    </div>
    ${offerHtml}
    ${_buildExtraProductsSection(d)}
    ${_buildAttachmentsSection(d.attachments ?? [])}
    ${_buildFormSection(d.status, d.notes ?? [])}`;
}

function _buildExtraProductsSection(d) {
    const extras = d.extra_products ?? [];
    const total  = parseFloat(d.extra_products_total ?? 0);

    const rows = extras.map(ep => `
        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
            <div class="flex-1 min-w-0 mr-3">
                <p class="text-sm font-medium text-gray-800 truncate">${esc(ep.name)}</p>
                <p class="text-xs text-gray-400">€ ${parseFloat(ep.price).toFixed(2)} × ${ep.quantity}</p>
            </div>
            <div class="flex items-center space-x-2 flex-shrink-0">
                <span class="text-sm font-semibold text-gray-700">€ ${parseFloat(ep.subtotal).toFixed(2)}</span>
                <button onclick="removeExtraProduct(${d.id},${ep.id})" class="p-1 text-red-400 active:text-red-600">
                    <i data-feather="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
        </div>`).join('');

    const prodOpts = (_availableProducts ?? []).map(p =>
        `<option value="${p.id}" data-price="${p.price}">[${p.type === 'supplement' ? 'Suppl.' : 'Prod.'}] ${esc(p.name)} — €${parseFloat(p.price).toFixed(2)}</option>`
    ).join('');

    return `<div class="space-y-3" id="sheet-extras-wrap">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Prodotti aggiuntivi</p>
        <div class="divide-y divide-gray-100 rounded-xl border border-gray-100 px-3">
            ${rows || '<p class="text-xs text-gray-400 py-3">Nessun prodotto aggiunto.</p>'}
        </div>
        <div class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-xl">
            <span class="text-sm font-semibold text-gray-600">Totale extra</span>
            <span class="text-sm font-bold text-gray-900" id="sheet-extras-total">€ ${total.toFixed(2)}</span>
        </div>
        <div class="space-y-2 pt-1">
            <select id="sheet-product-select" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-brand-400">
                <option value="">Seleziona prodotto…</option>${prodOpts}
            </select>
            <div class="flex items-center space-x-2">
                <input type="number" id="sheet-product-qty" value="1" min="1"
                    class="w-20 text-sm text-center border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-brand-400 bg-white">
                <button onclick="addExtraProduct(${d.id})" id="sheet-add-product-btn"
                    class="flex-1 text-sm font-semibold bg-brand-600 text-white py-2.5 rounded-xl active:bg-brand-700 disabled:opacity-50">Aggiungi</button>
            </div>
        </div>
        <p id="sheet-extra-fb" class="hidden text-xs text-green-600 font-medium">Prodotto aggiunto.</p>
    </div>`;
}

// ── Sheet actions ─────────────────────────────────────────────────────────────
async function saveSheetChanges() {
    const status    = document.getElementById('sheet-status').value;
    const noteInput = document.getElementById('sheet-note-input');
    const note      = (noteInput?.value ?? '').trim();
    const btn       = document.getElementById('sheet-save-btn');
    const fb        = document.getElementById('sheet-save-fb');

    if (_sheetType === 'activity' && status === 'suspended' && !note) {
        noteInput.classList.add('border-red-400', 'bg-red-50');
        noteInput.placeholder = 'Motivazione obbligatoria per la sospensione…';
        noteInput.focus();
        const errId = 'sheet-suspended-err';
        if (!document.getElementById(errId)) {
            const err = document.createElement('p');
            err.id = errId;
            err.className = 'text-xs text-red-500 font-medium';
            err.textContent = 'Inserisci una motivazione per lo stato Sospeso.';
            noteInput.insertAdjacentElement('afterend', err);
        }
        return;
    }
    // Rimuovi eventuale errore sospensione precedente
    document.getElementById('sheet-suspended-err')?.remove();
    if (noteInput) noteInput.classList.remove('border-red-400', 'bg-red-50');

    btn.disabled = true; btn.textContent = '…';

    try {
        if (_sheetType === 'calendar' || _sheetType === 'activity') {
            const url    = _sheetType === 'calendar'
                ? `/api/technician/calendar-events/${_sheetId}`
                : `/api/technician/cart-activities/${_sheetId}`;
            const body   = { status };
            if (note) body.note = note;
            const res = await fetch(url, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(body),
            });
            if (res.status === 401) { showSessionExpired(); return; }
            if (res.ok) {
                const json = await res.json();
                _sheetData = json.data ?? _sheetData;
                if (_sheetType === 'activity') {
                    fb.classList.remove('hidden');
                    await new Promise(r => setTimeout(r, 1200));
                    closeActivitySheet();
                    loadAgenda();
                    return;
                }
            }
        } else if (_sheetType === 'ticket') {
            const statusRes = await fetch(`/api/technician/tickets/${_sheetId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ ticket_status: status }),
            });
            if (statusRes.status === 401) { showSessionExpired(); return; }

            if (note) {
                const noteRes = await fetch(`/api/technician/tickets/${_sheetId}/notes`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ body: note }),
                });
                if (noteRes.status === 401) { showSessionExpired(); return; }
                if (noteRes.ok) {
                    const json = await noteRes.json();
                    if (!_sheetData.notes) _sheetData.notes = [];
                    _sheetData.notes.push(json.data ?? { body: note, created_at: new Date().toISOString() });
                }
            }
            if (statusRes.ok) {
                if (_sheetData) _sheetData.ticket_status = status;
                const t = allTickets.find(t => t.id === _sheetId);
                if (t) t.ticket_status = status;
                const c = document.getElementById('agenda-tickets-cards');
                if (c) { c.innerHTML = renderTicketCards(); feather.replace(); }
            }
        }

        _renderSheetContent();
        fb.classList.remove('hidden');
        setTimeout(() => fb.classList.add('hidden'), 2500);
    } catch (_) {}
    btn.disabled = false; btn.textContent = 'Salva';
}

async function uploadSheetImages(input) {
    if (!input.files.length) return;
    const txtEl = document.getElementById('sheet-upload-txt');
    const fb    = document.getElementById('sheet-upload-fb');
    txtEl.textContent = 'Caricamento…';

    const urls = {
        calendar: `/api/technician/calendar-events/${_sheetId}/attachments`,
        ticket:   `/api/technician/tickets/${_sheetId}/attachments`,
        activity: `/api/technician/cart-activities/${_sheetId}/attachments`,
    };

    const formData = new FormData();
    for (const f of input.files) formData.append('images[]', f);

    try {
        const res = await fetch(urls[_sheetType], { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, body: formData });
        if (res.status === 401) { showSessionExpired(); return; }
        if (res.ok) {
            const json = await res.json();
            if (_sheetData) {
                _sheetData.attachments = json.data?.attachments ?? _sheetData.attachments ?? [];
                const wrap = document.getElementById('sheet-attachments-wrap');
                if (wrap) { wrap.outerHTML = _buildAttachmentsSection(_sheetData.attachments); feather.replace(); }
            }
            fb.classList.remove('hidden');
            setTimeout(() => fb.classList.add('hidden'), 2500);
        }
    } catch (_) {}
    input.value = '';
    const txt2 = document.getElementById('sheet-upload-txt');
    if (txt2) txt2.textContent = 'Carica immagini';
}

async function _loadAvailableProducts() {
    try {
        const res = await fetch('/api/technician/products?types[]=product&types[]=supplement', { headers: { 'X-CSRF-TOKEN': CSRF } });
        if (res.ok) { const j = await res.json(); _availableProducts = j.data ?? []; }
        else _availableProducts = [];
    } catch (_) { _availableProducts = []; }
}

async function addExtraProduct(activityId) {
    const sel = document.getElementById('sheet-product-select');
    const qty = parseInt(document.getElementById('sheet-product-qty').value);
    const pid = parseInt(sel.value);
    if (!pid || qty < 1) return;

    const btn = document.getElementById('sheet-add-product-btn');
    const fb  = document.getElementById('sheet-extra-fb');
    btn.disabled = true; btn.textContent = '…';

    try {
        const res = await fetch(`/api/technician/cart-activities/${activityId}/extra-products`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ product_id: pid, quantity: qty }),
        });
        if (res.status === 401) { showSessionExpired(); return; }
        if (res.ok) {
            await _refreshExtras(activityId);
            fb.classList.remove('hidden');
            setTimeout(() => fb.classList.add('hidden'), 2500);
            document.getElementById('sheet-product-qty').value = 1;
        }
    } catch (_) {}
    btn.disabled = false; btn.textContent = 'Aggiungi';
}

async function removeExtraProduct(activityId, extraProductId) {
    try {
        const res = await fetch(`/api/technician/cart-activities/${activityId}/extra-products/${extraProductId}`, {
            method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF },
        });
        if (res.status === 401) { showSessionExpired(); return; }
        if (res.ok) await _refreshExtras(activityId);
    } catch (_) {}
}

async function _refreshExtras(activityId) {
    const res = await fetch(`/api/technician/cart-activities/${activityId}`, { headers: { 'X-CSRF-TOKEN': CSRF } });
    if (!res.ok) return;
    const json = await res.json();
    _sheetData = json.data ?? json;
    const wrap = document.getElementById('sheet-extras-wrap');
    if (wrap) { wrap.outerHTML = _buildExtraProductsSection(_sheetData); feather.replace(); }
}

// ── Init ──────────────────────────────────────────────────────────────────────
initAgendaDate();
loadAgenda();
</script>

@if($mapsApiKey)
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ $mapsApiKey }}&libraries=places&callback=onGoogleMapsLoaded">
</script>
@endif
</body>
</html>

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
            <img src="/piudsl.png" alt="PiùDSL" class="h-8 w-auto brightness-0 invert">
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

<!-- Main Content -->
<main class="content-area pt-14">

    <!-- ===== CALENDAR EVENTS ===== -->
    <section id="section-calendar" class="px-4 py-4 space-y-3">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-base font-semibold text-gray-700">Calendario</h2>
            <button onclick="loadCalendar()" class="text-brand-600 text-sm font-medium flex items-center space-x-1">
                <i data-feather="refresh-cw" class="w-3.5 h-3.5"></i>
                <span>Aggiorna</span>
            </button>
        </div>
        <div id="calendar-loading" class="space-y-3">
            <div class="skeleton h-28 rounded-2xl"></div>
            <div class="skeleton h-28 rounded-2xl"></div>
            <div class="skeleton h-28 rounded-2xl"></div>
        </div>
        <div id="calendar-error" class="hidden text-center py-10">
            <i data-feather="alert-circle" class="w-10 h-10 text-red-400 mx-auto mb-2"></i>
            <p class="text-gray-500 text-sm mb-3">Impossibile caricare il calendario.</p>
            <button onclick="loadCalendar()" class="text-brand-600 text-sm font-medium">Riprova</button>
        </div>
        <div id="calendar-empty" class="hidden text-center py-10">
            <i data-feather="calendar" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
            <p class="text-gray-400 text-sm">Nessun evento in programma.</p>
        </div>
        <div id="calendar-list" class="hidden space-y-3"></div>
    </section>

    <!-- ===== CART ACTIVITIES ===== -->
    <section id="section-activities" class="hidden px-4 py-4 space-y-3">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-base font-semibold text-gray-700">Attività</h2>
            <button onclick="loadActivities()" class="text-brand-600 text-sm font-medium flex items-center space-x-1">
                <i data-feather="refresh-cw" class="w-3.5 h-3.5"></i>
                <span>Aggiorna</span>
            </button>
        </div>
        <div id="activities-loading" class="space-y-3">
            <div class="skeleton h-28 rounded-2xl"></div>
            <div class="skeleton h-28 rounded-2xl"></div>
        </div>
        <div id="activities-error" class="hidden text-center py-10">
            <i data-feather="alert-circle" class="w-10 h-10 text-red-400 mx-auto mb-2"></i>
            <p class="text-gray-500 text-sm mb-3">Impossibile caricare le attività.</p>
            <button onclick="loadActivities()" class="text-brand-600 text-sm font-medium">Riprova</button>
        </div>
        <div id="activities-empty" class="hidden text-center py-10">
            <i data-feather="tool" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
            <p class="text-gray-400 text-sm">Nessuna attività assegnata.</p>
        </div>
        <div id="activities-list" class="hidden space-y-3"></div>
    </section>

    <!-- ===== FATTURE CARTACEE ===== -->
    <section id="section-invoices" class="hidden px-4 py-4 space-y-3">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-base font-semibold text-gray-700">Fatture cartacee</h2>
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

    <!-- ===== TICKETS ===== -->
    <section id="section-tickets" class="hidden px-4 py-4 space-y-3">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-base font-semibold text-gray-700">Ticket</h2>
            <button onclick="loadTickets()" class="text-brand-600 text-sm font-medium flex items-center space-x-1">
                <i data-feather="refresh-cw" class="w-3.5 h-3.5"></i>
                <span>Aggiorna</span>
            </button>
        </div>

        <!-- Filter pills -->
        <div class="flex space-x-2 overflow-x-auto pb-1 scrollbar-hide">
            <button onclick="filterTickets('all')"     id="filter-all"     class="ticket-filter flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-brand-600 text-white">Tutti</button>
            <button onclick="filterTickets('open')"    id="filter-open"    class="ticket-filter flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-600">Aperti</button>
            <button onclick="filterTickets('pending')" id="filter-pending" class="ticket-filter flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-600">In attesa</button>
            <button onclick="filterTickets('close')"   id="filter-close"   class="ticket-filter flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-600">Chiusi</button>
        </div>

        <div id="tickets-loading" class="space-y-3">
            <div class="skeleton h-24 rounded-2xl"></div>
            <div class="skeleton h-24 rounded-2xl"></div>
            <div class="skeleton h-24 rounded-2xl"></div>
        </div>
        <div id="tickets-error" class="hidden text-center py-10">
            <i data-feather="alert-circle" class="w-10 h-10 text-red-400 mx-auto mb-2"></i>
            <p class="text-gray-500 text-sm mb-3">Impossibile caricare i ticket.</p>
            <button onclick="loadTickets()" class="text-brand-600 text-sm font-medium">Riprova</button>
        </div>
        <div id="tickets-empty" class="hidden text-center py-10">
            <i data-feather="inbox" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
            <p class="text-gray-400 text-sm">Nessun ticket trovato.</p>
        </div>
        <div id="tickets-list" class="hidden space-y-3"></div>
    </section>

</main>

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
<nav class="tab-bar fixed bottom-0 left-0 right-0 z-40 bg-white grid grid-cols-4">
    <button id="tab-calendar"   onclick="switchTab('calendar')"   class="tab-active   flex flex-col items-center justify-center pt-2 pb-1 space-y-0.5 transition-colors">
        <i data-feather="calendar"  class="w-5 h-5"></i>
        <span class="text-[10px] font-semibold">Calendario</span>
    </button>
    <button id="tab-activities" onclick="switchTab('activities')" class="tab-inactive flex flex-col items-center justify-center pt-2 pb-1 space-y-0.5 transition-colors">
        <i data-feather="tool"      class="w-5 h-5"></i>
        <span class="text-[10px] font-semibold">Attività</span>
    </button>
    <button id="tab-invoices"   onclick="switchTab('invoices')"   class="tab-inactive flex flex-col items-center justify-center pt-2 pb-1 space-y-0.5 transition-colors">
        <i data-feather="file-text" class="w-5 h-5"></i>
        <span class="text-[10px] font-semibold">Fatture</span>
    </button>
    <button id="tab-tickets"    onclick="switchTab('tickets')"    class="tab-inactive flex flex-col items-center justify-center pt-2 pb-1 space-y-0.5 transition-colors">
        <i data-feather="message-square" class="w-5 h-5"></i>
        <span class="text-[10px] font-semibold">Ticket</span>
    </button>
</nav>

<script>
feather.replace();

const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ── Loaded flags (lazy loading) ──────────────────────────────────────────────
const loaded = { calendar: false, activities: false, invoices: false, tickets: false };

// ── All tickets data (for client-side filtering) ─────────────────────────────
let allTickets = [];
let activeFilter = 'all';

// ── Session expired ──────────────────────────────────────────────────────────
function showSessionExpired() {
    document.getElementById('session-expired').classList.remove('hidden');
}

// ── Tab switching ─────────────────────────────────────────────────────────────
const tabs     = ['calendar', 'activities', 'invoices', 'tickets'];
const sections = { calendar: 'section-calendar', activities: 'section-activities', invoices: 'section-invoices', tickets: 'section-tickets' };

function switchTab(name) {
    tabs.forEach(t => {
        document.getElementById('tab-' + t).className =
            (t === name ? 'tab-active' : 'tab-inactive') +
            ' flex flex-col items-center justify-center pt-2 pb-1 space-y-0.5 transition-colors';
        document.getElementById(sections[t]).classList.toggle('hidden', t !== name);
    });

    if (!loaded[name]) {
        if (name === 'calendar')   loadCalendar();
        if (name === 'activities') loadActivities();
        if (name === 'invoices')   loadInvoicesFirstTime();
        if (name === 'tickets')    loadTickets();
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function showState(prefix, state) {
    ['loading', 'error', 'empty', 'list'].forEach(s => {
        const el = document.getElementById(prefix + '-' + s);
        if (el) el.classList.toggle('hidden', s !== state);
    });
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

// ── CALENDAR EVENTS ───────────────────────────────────────────────────────────
async function loadCalendar() {
    showState('calendar', 'loading');
    loaded.calendar = false;

    try {
        const res  = await fetch('/api/technician/calendar-events', { headers: { 'X-CSRF-TOKEN': CSRF } });
        if (res.status === 401) { showSessionExpired(); return; }

        const json = await res.json();
        const data = json.data ?? [];

        if (!data.length) { showState('calendar', 'empty'); loaded.calendar = true; return; }

        data.sort((a, b) => (a.start_date + a.start_time) > (b.start_date + b.start_time) ? 1 : -1);

        const list = document.getElementById('calendar-list');
        list.innerHTML = data.map(ev => {
            const color = ev.color || '#0284c7';
            const histories = (ev.histories ?? []).map(h =>
                `<li class="text-xs text-gray-500 border-l-2 border-gray-200 pl-2 py-0.5">${h.note} <span class="text-gray-400 text-[10px]">${formatDate(h.created_at)}</span></li>`
            ).join('');

            return `
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="h-1" style="background:${color}"></div>
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="font-semibold text-gray-900 text-sm leading-tight">${ev.title}</span>
                        ${statusBadge(ev.status)}
                    </div>
                    <div class="flex items-center text-xs text-gray-500 space-x-3 mb-1">
                        <span class="flex items-center space-x-1">
                            <i data-feather="clock" class="w-3 h-3"></i>
                            <span>${formatDate(ev.start_date, ev.start_time)} → ${formatDate(ev.end_date, ev.end_time)}</span>
                        </span>
                    </div>
                    ${ev.customer ? `<div class="flex items-center text-xs text-gray-500 space-x-1 mb-2"><i data-feather="user" class="w-3 h-3"></i><span>${ev.customer}</span></div>` : ''}
                    ${ev.description ? `<p class="text-xs text-gray-400 mb-2">${ev.description}</p>` : ''}
                    ${histories ? `
                    <details class="mt-1">
                        <summary class="text-xs text-brand-600 cursor-pointer font-medium">Note (${ev.histories.length})</summary>
                        <ul class="mt-2 space-y-1">${histories}</ul>
                    </details>` : ''}
                </div>
            </div>`;
        }).join('');

        showState('calendar', 'list');
        loaded.calendar = true;
        feather.replace();

    } catch (e) {
        showState('calendar', 'error');
    }
}

// ── CART ACTIVITIES ───────────────────────────────────────────────────────────
async function loadActivities() {
    showState('activities', 'loading');
    loaded.activities = false;

    try {
        const res  = await fetch('/api/technician/cart-activities', { headers: { 'X-CSRF-TOKEN': CSRF } });
        if (res.status === 401) { showSessionExpired(); return; }

        const json = await res.json();
        const data = json.data ?? [];

        if (!data.length) { showState('activities', 'empty'); loaded.activities = true; return; }

        data.sort((a, b) => (a.event_at + a.event_time) > (b.event_at + b.event_time) ? 1 : -1);

        const today = new Date().toISOString().slice(0, 10);

        const list = document.getElementById('activities-list');
        list.innerHTML = data.map(act => {
            const isToday = act.event_at === today;
            const mapsUrl = act.coordinates
                ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(act.coordinates)}`
                : `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(act.full_address ?? '')}`;

            return `
            <div class="bg-white rounded-2xl shadow-sm border ${isToday ? 'border-brand-300' : 'border-gray-100'} overflow-hidden">
                ${isToday ? '<div class="bg-brand-600 text-white text-[10px] font-bold px-4 py-1">OGGI</div>' : ''}
                <div class="p-4">
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
                        <span>${formatDate(act.event_at)} ${act.event_time ? act.event_time.slice(0,5) : ''}</span>
                    </div>
                    ${act.note ? `<p class="text-xs text-gray-400 mt-2">${act.note}</p>` : ''}
                </div>
            </div>`;
        }).join('');

        showState('activities', 'list');
        loaded.activities = true;
        feather.replace();

    } catch (e) {
        showState('activities', 'error');
    }
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

            const directionsUrl = (inv.coordinates && prevCoords)
                ? `https://www.google.com/maps/dir/?api=1&origin=${encodeURIComponent(prevCoords)}&destination=${encodeURIComponent(inv.coordinates)}&travelmode=driving`
                : null;

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
                    ${directionsUrl ? `
                    <a href="${directionsUrl}" target="_blank" rel="noopener"
                        class="mb-3 flex items-center space-x-1.5 text-xs text-brand-600 font-medium active:opacity-70">
                        <i data-feather="navigation" class="w-3.5 h-3.5 flex-shrink-0"></i>
                        <span class="underline underline-offset-2">Avvia itinerario</span>
                    </a>` : ''}

                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Importo</span>
                        <span class="text-sm font-bold text-gray-800">€ ${Number(inv.amount).toFixed(2)}</span>
                    </div>

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

// ── TICKETS ───────────────────────────────────────────────────────────────────
async function loadTickets() {
    showState('tickets', 'loading');
    loaded.tickets = false;
    allTickets = [];

    try {
        const res  = await fetch('/api/technician/tickets', { headers: { 'X-CSRF-TOKEN': CSRF } });
        if (res.status === 401) { showSessionExpired(); return; }

        const json = await res.json();
        allTickets = json.data ?? [];
        loaded.tickets = true;

        renderTickets();

    } catch (e) {
        showState('tickets', 'error');
    }
}

function filterTickets(status) {
    activeFilter = status;
    document.querySelectorAll('.ticket-filter').forEach(btn => {
        btn.className = 'ticket-filter flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full ' +
            (btn.id === 'filter-' + status ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600');
    });
    renderTickets();
}

function renderTickets() {
    const filtered = activeFilter === 'all' ? allTickets : allTickets.filter(t => t.ticket_status === activeFilter);

    if (!filtered.length) { showState('tickets', 'empty'); return; }

    const list = document.getElementById('tickets-list');
    list.innerHTML = filtered.map(t => `
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4" id="ticket-${t.id}">
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

            <!-- Inline status update -->
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
        </div>
    `).join('');

    showState('tickets', 'list');
    feather.replace();
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

// ── Init ──────────────────────────────────────────────────────────────────────
loadCalendar();
</script>

@if($mapsApiKey)
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ $mapsApiKey }}&libraries=places&callback=onGoogleMapsLoaded">
</script>
@endif
</body>
</html>

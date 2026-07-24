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
    <meta name="apple-mobile-web-app-title" content="PiùDSL Tecnico">
    <meta name="theme-color" content="#0284c7">

    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">

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
        .content-area { padding-bottom: env(safe-area-inset-bottom); }

        #side-menu-overlay { transition: opacity 0.25s ease; }
        #side-menu-overlay.is-open { opacity: 1; }
        #side-menu-overlay:not(.is-open) { opacity: 0; pointer-events: none; }
        #side-menu { transition: transform 0.28s cubic-bezier(0.32, 0.72, 0, 1); }
        #side-menu.is-open { transform: translateX(0); }
        #side-menu:not(.is-open) { transform: translateX(-100%); }

        .side-menu-item {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            width: 100%;
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            text-align: left;
            font-size: 0.9375rem;
            font-weight: 500;
            color: #334155;
            transition: background 0.15s;
        }
        .side-menu-item:active { background: #f1f5f9; }
        .side-menu-item.is-active { background: #f0f9ff; color: #0369a1; }
        .side-menu-item .side-menu-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.625rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .skeleton { background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

        /* Ricerca — stile Google Calendar */
        .search-row-date-day {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 1.375rem;
            line-height: 1;
            color: #3c4043;
        }
        .search-row-date-day.is-today {
            background: #1a73e8;
            color: #fff;
            border-radius: 9999px;
            font-size: 1.125rem;
            font-weight: 500;
        }
        .search-row-time {
            min-width: 6.75rem;
            padding-top: 0.375rem;
        }

        /* Vista mese — stile Google Calendar */
        .cal-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            border-bottom: 1px solid #dadce0;
            background: #fff;
        }
        .cal-weekdays span {
            text-align: center;
            font-size: 10px;
            font-weight: 600;
            color: #70757a;
            padding: 8px 0 6px;
            letter-spacing: 0.02em;
        }
        .cal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            border-left: 1px solid #dadce0;
            background: #fff;
        }
        .cal-day-cell {
            min-height: 5.75rem;
            border-right: 1px solid #dadce0;
            border-bottom: 1px solid #dadce0;
            padding: 2px 3px 4px;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            overflow: hidden;
            -webkit-tap-highlight-color: transparent;
        }
        .cal-day-cell.other-month { background: #fafafa; }
        .cal-day-cell.other-month .cal-day-num { color: #80868b; }
        .cal-day-head {
            display: flex;
            justify-content: center;
            padding: 4px 0 2px;
            flex-shrink: 0;
            cursor: pointer;
            border-radius: 4px;
        }
        .cal-day-head:active { opacity: 0.65; }
        .cal-day-num {
            width: 1.625rem;
            height: 1.625rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 500;
            color: #3c4043;
            border-radius: 50%;
            line-height: 1;
        }
        .cal-day-num.today {
            background: #1a73e8;
            color: #fff;
            font-weight: 600;
        }
        .cal-day-events {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
            flex: 1;
        }
        .cal-event-line {
            display: flex;
            align-items: flex-start;
            gap: 3px;
            min-width: 0;
            padding: 0 1px;
            line-height: 1.2;
        }
        .cal-event-line-btn {
            cursor: pointer;
            border-radius: 2px;
            padding: 1px;
            margin: -1px;
        }
        .cal-event-line-btn:active { background: #e8f0fe; }
        .cal-event-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 3px;
        }
        .cal-event-text {
            font-size: 9px;
            color: #3c4043;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
            flex: 1;
        }
        .cal-event-time {
            font-weight: 600;
            color: #202124;
        }
        .cal-day-cell.is-past .cal-event-text,
        .cal-day-cell.is-past .cal-event-time,
        .cal-day-cell.is-past .cal-more-link {
            opacity: 0.45;
        }
        .cal-more-link {
            font-size: 9px;
            color: #70757a;
            padding: 2px 1px 0;
            white-space: nowrap;
            cursor: pointer;
        }
        .cal-more-link:active { color: #1a73e8; }
    </style>

    @if(config('services.onesignal.app_id'))
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    @endif
</head>
<body class="font-display antialiased bg-gray-50 text-gray-900">

<!-- Fixed Header -->
<header id="main-header" class="fixed top-0 left-0 right-0 z-40 bg-brand-700 text-white shadow-lg" style="padding-top: env(safe-area-inset-top)">
    <div class="flex items-center justify-between gap-2 px-3 h-14 min-w-0">
        <div class="flex items-center gap-1 min-w-0 flex-shrink">
            <button type="button" onclick="openSideMenu()"
                    class="p-2 -ml-1 rounded-lg hover:bg-white/10 active:bg-white/15 transition-colors flex-shrink-0"
                    aria-label="Menu">
                <i data-feather="menu" class="w-5 h-5"></i>
            </button>
            <img src="/piudsl.png" alt="PiùDSL" class="h-7 w-auto flex-shrink-0">
            <span class="text-xs sm:text-sm font-medium text-brand-100 truncate">{{ $userName }}</span>
        </div>

        <div class="flex items-center gap-0.5 flex-shrink-0">
            <button type="button" onclick="openSearchPanel()"
                    class="flex flex-col items-center justify-center min-w-[2.75rem] px-1 py-1 rounded-lg hover:bg-white/10 active:bg-white/15 transition-colors"
                    aria-label="Cerca">
                <i data-feather="search" class="w-4 h-4"></i>
                <span class="text-[9px] font-semibold leading-tight mt-0.5">Cerca</span>
            </button>
            <button type="button" onclick="refreshCurrentTab()"
                    class="flex flex-col items-center justify-center min-w-[2.75rem] px-1 py-1 rounded-lg hover:bg-white/10 active:bg-white/15 transition-colors"
                    aria-label="Aggiorna">
                <i data-feather="refresh-cw" class="w-4 h-4"></i>
                <span class="text-[9px] font-semibold leading-tight mt-0.5">Aggiorna</span>
            </button>
            <form method="POST" action="/auth/logout" id="logout-form" class="contents">
                @csrf
                <button type="button" onclick="handleLogout()"
                        class="flex flex-col items-center justify-center min-w-[2.75rem] px-1 py-1 rounded-lg hover:bg-white/10 active:bg-white/15 transition-colors"
                        aria-label="Logout">
                    <i data-feather="log-out" class="w-4 h-4"></i>
                    <span class="text-[9px] font-semibold leading-tight mt-0.5">Esci</span>
                </button>
            </form>
        </div>
    </div>
    <div id="tech-cash-bar" class="hidden px-3 py-1.5 bg-brand-800 border-t border-white/10 flex items-center justify-between gap-2">
        <span class="flex items-center gap-1.5 text-[11px] text-brand-100 font-medium min-w-0">
            <i data-feather="dollar-sign" class="w-3.5 h-3.5 flex-shrink-0"></i>
            <span class="truncate">Da consegnare oggi</span>
        </span>
        <span id="tech-cash-total" class="text-sm font-bold text-white flex-shrink-0">—</span>
    </div>
</header>

<!-- Side Menu -->
<div id="side-menu-overlay" class="fixed inset-0 z-[55] bg-black/40" onclick="closeSideMenu()" aria-hidden="true"></div>
<aside id="side-menu" class="fixed top-0 left-0 bottom-0 z-[56] w-[min(18.5rem,88vw)] bg-white shadow-2xl flex flex-col"
       style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom);" aria-hidden="true">
    <div class="flex items-center justify-between px-4 h-14 border-b border-gray-100 flex-shrink-0">
        <span class="text-base font-bold text-gray-900">Menu</span>
        <button type="button" onclick="closeSideMenu()" class="p-2 -mr-1 rounded-full text-gray-400 active:bg-gray-100" aria-label="Chiudi menu">
            <i data-feather="x" class="w-5 h-5"></i>
        </button>
    </div>
    <nav class="flex-1 overflow-y-auto px-2 py-3 space-y-1">
        <button type="button" onclick="menuOpenCalOverview()" class="side-menu-item">
            <span class="side-menu-icon bg-sky-50 text-sky-600"><i data-feather="calendar" class="w-5 h-5"></i></span>
            <span class="flex-1">Calendario</span>
        </button>
        <button type="button" id="side-menu-invoices" onclick="menuNavigateInvoices()" class="side-menu-item">
            <span class="side-menu-icon bg-brand-50 text-brand-600"><i data-feather="file-text" class="w-5 h-5"></i></span>
            <span class="flex-1">Consegna fatture</span>
        </button>
        <button type="button" id="side-menu-recoveries" onclick="menuNavigateRecoveries()" class="side-menu-item">
            <span class="side-menu-icon bg-red-50 text-red-600"><i data-feather="package" class="w-5 h-5"></i></span>
            <span class="flex-1">Recupero Impianti</span>
        </button>
        <button type="button" onclick="menuOpenSegnala()" class="side-menu-item">
            <span class="side-menu-icon bg-orange-50 text-orange-500"><i data-feather="alert-triangle" class="w-5 h-5"></i></span>
            <span class="flex-1">Segnala</span>
        </button>
        @if(config('services.onesignal.app_id'))
        <button type="button" id="side-menu-notif" onclick="handleNotifToggle()" class="side-menu-item hidden">
            <span class="side-menu-icon bg-violet-50 text-violet-600"><i data-feather="bell-off" class="w-5 h-5"></i></span>
            <span class="flex-1 text-left">
                <span class="block">Notifiche push</span>
                <span id="side-menu-notif-status" class="block text-xs font-normal text-gray-400 mt-0.5">Disattive</span>
            </span>
        </button>
        @endif
    </nav>
    <div class="flex-shrink-0 px-4 py-3 border-t border-gray-100">
        <p class="text-xs text-gray-400 truncate">{{ $userName }}</p>
    </div>
</aside>

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

<!-- Search Panel -->
<div id="search-panel" class="hidden fixed inset-x-0 bottom-0 z-30 flex flex-col bg-white" style="top: calc(3.5rem + env(safe-area-inset-top))">
    <div class="flex-shrink-0 flex items-center gap-2 px-3 py-2.5 border-b border-gray-200 bg-white shadow-sm">
        <button type="button" onclick="closeSearchPanel()" class="p-2 -ml-1 rounded-full text-gray-500 active:bg-gray-100" aria-label="Chiudi ricerca">
            <i data-feather="arrow-left" class="w-5 h-5"></i>
        </button>
        <div class="flex-1 relative flex items-center min-w-0">
            <i data-feather="search" class="absolute left-3 w-4 h-4 text-gray-400 pointer-events-none"></i>
            <input type="search" id="search-input" enterkeyhint="search" autocomplete="off"
                placeholder="Cerca interventi, clienti, indirizzi, note…"
                class="w-full text-sm pl-9 pr-9 py-2.5 bg-gray-100 rounded-full border-0 focus:outline-none focus:ring-2 focus:ring-brand-400/40 focus:bg-white">
            <button type="button" id="search-clear" onclick="clearSearchInput()"
                class="hidden absolute right-2 p-1.5 rounded-full text-gray-400 active:bg-gray-200">
                <i data-feather="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
    <div id="search-hint" class="flex-1 flex items-center justify-center px-6 text-center">
        <p class="text-sm text-gray-400">Cerca in ordini, eventi e ticket assegnati a te.<br>Nomi, indirizzi, note, titoli…</p>
    </div>
    <div id="search-loading" class="hidden flex-1 flex items-center justify-center">
        <div class="skeleton h-12 rounded-xl w-full max-w-sm mx-4"></div>
    </div>
    <div id="search-empty" class="hidden flex-1 flex flex-col items-center justify-center px-6 text-center">
        <i data-feather="search" class="w-10 h-10 text-gray-300 mb-2"></i>
        <p class="text-sm text-gray-500">Nessun risultato per questa ricerca.</p>
    </div>
    <div id="search-results" class="hidden flex-1 overflow-y-auto" style="padding-bottom: env(safe-area-inset-bottom)"></div>
</div>

<!-- Main Content -->
<main id="main-content" class="content-area pt-14">

    <!-- ===== AGENDA — vista giornaliera ===== -->
    <section id="section-agenda" class="px-4 py-4 space-y-3">
        <div class="mb-1">
            <h2 class="text-base font-semibold text-gray-700">Interventi</h2>
            <p id="agenda-date-label" class="text-xs text-gray-400 capitalize mt-0.5"></p>
        </div>

        <!-- Navigatore giorno -->
        <div class="flex items-center bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <button onclick="shiftDate(-1)" class="px-4 py-2.5 text-gray-400 active:bg-gray-50 transition-colors" aria-label="Giorno precedente">
                <i data-feather="chevron-left" class="w-4 h-4"></i>
            </button>
            <input type="date" id="agenda-date" onchange="onDateChange()"
                   class="flex-1 text-center text-sm font-semibold text-gray-800 focus:outline-none bg-transparent py-2.5 cursor-pointer">
            <button onclick="shiftDate(1)" class="px-4 py-2.5 text-gray-400 active:bg-gray-50 transition-colors" aria-label="Giorno successivo">
                <i data-feather="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="flex justify-center">
            <button id="today-btn" onclick="goToToday()"
                class="text-xs font-semibold text-brand-600 px-4 py-1.5 rounded-full border border-brand-200 bg-brand-50 active:bg-brand-100 transition-opacity">
                Oggi
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
            <p class="text-gray-400 text-sm">Nessun intervento per questa data.</p>
        </div>
        <div id="agenda-list" class="hidden space-y-3"></div>
    </section>

    <!-- ===== FATTURE CARTACEE ===== -->
    <section id="section-invoices" class="hidden px-4 py-4 space-y-3">
        <button type="button" onclick="switchTab('agenda')"
            class="flex items-center gap-1 text-sm font-medium text-brand-600 -mt-1 mb-1 active:opacity-70">
            <i data-feather="arrow-left" class="w-4 h-4"></i>
            <span>Interventi</span>
        </button>
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-base font-semibold text-gray-700">Consegna Fatture cartacee</h2>
            <button id="toggle-delivered-btn" onclick="toggleDelivered()"
                class="text-xs text-gray-400 font-medium flex items-center space-x-1">
                <i data-feather="eye" class="w-3.5 h-3.5"></i>
                <span>Consegnate</span>
            </button>
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

        <div class="relative">
            <i data-feather="search" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"></i>
            <input id="invoice-customer-search" type="search" placeholder="Cerca per nome cliente…"
                oninput="onInvoiceCustomerSearchInput()"
                class="w-full text-xs pl-8 pr-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-400 bg-gray-50">
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
        <div id="invoices-search-empty" class="hidden text-center py-10">
            <i data-feather="search" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
            <p class="text-gray-400 text-sm">Nessun cliente corrisponde alla ricerca.</p>
        </div>
        <div id="invoices-list" class="hidden space-y-3"></div>
    </section>

    <!-- ===== RECUPERO IMPIANTI ===== -->
    <section id="section-recoveries" class="hidden px-4 py-4 space-y-3">
        <button type="button" onclick="switchTab('agenda')"
            class="flex items-center gap-1 text-sm font-medium text-brand-600 -mt-1 mb-1 active:opacity-70">
            <i data-feather="arrow-left" class="w-4 h-4"></i>
            <span>Interventi</span>
        </button>
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-base font-semibold text-gray-700">Recupero Impianti</h2>
            <button id="toggle-completed-rec-btn" onclick="toggleCompletedRecoveries()"
                class="text-xs text-gray-400 font-medium flex items-center space-x-1">
                <i data-feather="eye" class="w-3.5 h-3.5"></i>
                <span>Completati</span>
            </button>
        </div>

        <div class="flex items-center space-x-2">
            <div class="relative flex-1">
                <i data-feather="map-pin" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"></i>
                <input id="recovery-address-input" type="text" placeholder="Indirizzo o posizione…"
                    class="w-full text-xs pl-7 pr-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-400 bg-gray-50">
            </div>
            <button id="recovery-gps-btn" onclick="detectRecoveryLocation()"
                class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-xl bg-brand-600 text-white active:bg-brand-700 transition-colors"
                aria-label="Usa la mia posizione">
                <i data-feather="crosshair" class="w-4 h-4"></i>
            </button>
        </div>

        <div id="recoveries-loading" class="space-y-3">
            <div class="skeleton h-24 rounded-2xl"></div>
            <div class="skeleton h-24 rounded-2xl"></div>
            <div class="skeleton h-24 rounded-2xl"></div>
        </div>
        <div id="recoveries-error" class="hidden text-center py-10">
            <i data-feather="alert-circle" class="w-10 h-10 text-red-400 mx-auto mb-2"></i>
            <p class="text-gray-500 text-sm mb-3">Impossibile caricare i recuperi.</p>
            <button onclick="loadRecoveries()" class="text-brand-600 text-sm font-medium">Riprova</button>
        </div>
        <div id="recoveries-empty" class="hidden text-center py-10">
            <i data-feather="package" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
            <p class="text-gray-400 text-sm">Nessun recupero impianto in sospeso.</p>
        </div>
        <div id="recoveries-list" class="hidden space-y-3"></div>
    </section>


</main>

<!-- ===== ACTIVITY DETAIL SHEET ===== -->
<div id="activity-sheet" class="hidden fixed inset-0 z-[60]">
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
    <span id="deliver-toast-msg">Operazione completata</span>
</div>

<!-- ===== RECUPERO IMPIANTI DETAIL SHEET ===== -->
<div id="recovery-sheet" class="hidden fixed inset-0 z-[60]">
    <div class="absolute inset-0 bg-black/50" onclick="closeRecoverySheet()"></div>
    <div id="recovery-sheet-panel"
         class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl flex flex-col shadow-2xl"
         style="max-height:92vh; transform:translateY(100%); transition:transform 0.35s cubic-bezier(0.32,0.72,0,1)">
        <div class="flex-shrink-0 flex justify-center py-3 cursor-pointer" onclick="closeRecoverySheet()">
            <div class="w-10 h-1 rounded-full bg-gray-300"></div>
        </div>
        <div class="flex-shrink-0 flex items-center justify-between px-5 pb-3 border-b border-gray-100">
            <div id="recovery-sheet-badge" class="flex items-center space-x-2 min-w-0"></div>
            <button onclick="closeRecoverySheet()" class="p-2 -mr-2 rounded-xl text-gray-400 active:bg-gray-100">
                <i data-feather="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto" style="padding-bottom: env(safe-area-inset-bottom)">
            <div id="recovery-sheet-loading" class="px-5 py-6 space-y-3">
                <div class="skeleton h-5 rounded-lg w-3/4"></div>
                <div class="skeleton h-4 rounded-lg w-1/2"></div>
                <div class="skeleton h-24 rounded-xl w-full mt-2"></div>
            </div>
            <div id="recovery-sheet-content" class="hidden px-5 pt-4 space-y-5" style="padding-bottom:2.5rem"></div>
        </div>
    </div>
</div>

<!-- Modale fattura saldata (recupero impianti) -->
<div id="recovery-payment-modal" class="hidden fixed inset-0 z-[70] bg-black/60 flex items-end justify-center p-4 pb-8">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
        <div class="px-5 pt-5 pb-3 border-b border-gray-100">
            <p class="text-base font-bold text-gray-900 tracking-wide">LA FATTURA È STATA PAGATA?</p>
            <p id="recovery-payment-unpaid-summary" class="text-xs text-red-700 mt-2 leading-snug"></p>
        </div>
        <div class="px-5 py-4 space-y-4">
            <div class="grid grid-cols-2 gap-2">
                <button type="button" id="recovery-payment-yes" onclick="setRecoveryPaymentAnswer(true)"
                    class="py-3 rounded-xl border-2 border-gray-200 text-sm font-semibold text-gray-700 active:bg-gray-50">
                    Sì, pagata
                </button>
                <button type="button" id="recovery-payment-no" onclick="setRecoveryPaymentAnswer(false)"
                    class="py-3 rounded-xl border-2 border-gray-200 text-sm font-semibold text-gray-700 active:bg-gray-50">
                    No, non pagata
                </button>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wide">Info per il backoffice</label>
                <div id="recovery-payment-invoice-wrap" class="hidden space-y-1">
                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Fattura saldata</label>
                    <select id="recovery-payment-invoice-id"
                        class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-brand-400 bg-gray-50"></select>
                </div>
                <textarea id="recovery-payment-info" rows="3"
                    placeholder="Es. pagato in contanti, ricevuta consegnata, cliente pagherà in sede…"
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 resize-none focus:outline-none focus:border-brand-400 bg-gray-50"></textarea>
                <p class="text-[10px] text-gray-400">Visibile nei log dell'ordine nel gestionale.</p>
            </div>
            <p id="recovery-payment-err" class="hidden text-xs text-red-500 font-medium"></p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="closeRecoveryPaymentModal()"
                    class="py-3 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 active:bg-gray-50">
                    Annulla
                </button>
                <button type="button" id="recovery-payment-confirm-btn" onclick="confirmRecoveryCompleteWithPayment()"
                    class="py-3 rounded-xl bg-green-600 active:bg-green-700 text-white text-sm font-semibold">
                    Conferma recupero
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modale fattura saldata (consegna cartacea) -->
<div id="invoice-payment-modal" class="hidden fixed inset-0 z-[70] bg-black/60 flex items-end justify-center p-4 pb-8">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
        <div class="px-5 pt-5 pb-3 border-b border-gray-100">
            <p class="text-base font-bold text-gray-900 tracking-wide">LA FATTURA È STATA PAGATA?</p>
            <p id="invoice-payment-summary" class="text-xs text-red-700 mt-2 leading-snug font-medium"></p>
        </div>
        <div class="px-5 py-4 space-y-4">
            <div class="grid grid-cols-2 gap-2">
                <button type="button" id="invoice-payment-yes" onclick="setInvoicePaymentAnswer(true)"
                    class="py-3 rounded-xl border-2 border-gray-200 text-sm font-semibold text-gray-700 active:bg-gray-50">
                    Sì, pagata
                </button>
                <button type="button" id="invoice-payment-no" onclick="setInvoicePaymentAnswer(false)"
                    class="py-3 rounded-xl border-2 border-gray-200 text-sm font-semibold text-gray-700 active:bg-gray-50">
                    No, non pagata
                </button>
            </div>
            <label id="invoice-payment-cash-wrap" class="hidden flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-3 py-3 cursor-pointer">
                <input type="checkbox" id="invoice-payment-cash" class="mt-0.5 rounded border-gray-300 text-green-600 focus:ring-green-500"
                    onchange="syncInvoicePaymentCashToggle()">
                <span class="text-sm font-semibold text-green-800 leading-snug">Pagata al tecnico in contanti</span>
            </label>
            <div id="invoice-payment-note-wrap" class="hidden space-y-1.5">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wide">Nota pagamento (opzionale)</label>
                <textarea id="invoice-payment-note" rows="2"
                    placeholder="Es. pagato in contanti alla consegna…"
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 resize-none focus:outline-none focus:border-brand-400 bg-gray-50"></textarea>
            </div>
            <p id="invoice-payment-err" class="hidden text-xs text-red-500 font-medium"></p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="closeInvoicePaymentModal()"
                    class="py-3 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 active:bg-gray-50">
                    Annulla
                </button>
                <button type="button" onclick="confirmInvoiceDeliverWithPayment()"
                    class="py-3 rounded-xl bg-green-600 active:bg-green-700 text-white text-sm font-semibold">
                    Conferma consegna
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modale incasso tardivo (fattura già consegnata) -->
<div id="invoice-late-payment-modal" class="hidden fixed inset-0 z-[70] bg-black/60 flex items-end justify-center p-4 pb-8">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
        <div class="px-5 pt-5 pb-3 border-b border-gray-100">
            <p class="text-base font-bold text-gray-900 tracking-wide">SEGNA PAGATO IN CONTANTI</p>
            <p id="invoice-late-payment-summary" class="text-xs text-red-700 mt-2 leading-snug font-medium"></p>
            <p class="text-[11px] text-gray-500 mt-2 leading-snug">Consegna già registrata: usa questo pulsante se il cliente ha pagato dopo.</p>
        </div>
        <div class="px-5 py-4 space-y-4">
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wide">Nota pagamento (opzionale)</label>
                <textarea id="invoice-late-payment-note" rows="2"
                    placeholder="Es. pagato in contanti dopo la consegna…"
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 resize-none focus:outline-none focus:border-brand-400 bg-gray-50"></textarea>
            </div>
            <p id="invoice-late-payment-err" class="hidden text-xs text-red-500 font-medium"></p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="closeInvoiceLatePaymentModal()"
                    class="py-3 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 active:bg-gray-50">
                    Annulla
                </button>
                <button type="button" onclick="confirmInvoiceLatePayment()"
                    class="py-3 rounded-xl bg-amber-500 active:bg-amber-600 text-white text-sm font-semibold">
                    Conferma incasso
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== CALENDAR MODAL (Mese / Anno) ===== -->
<div id="cal-overview" class="hidden fixed inset-x-0 bottom-0 z-30 flex flex-col bg-gray-50" style="top: calc(3.5rem + env(safe-area-inset-top))">
    <div class="flex-shrink-0 flex items-center justify-between px-4 h-14 bg-brand-700 text-white shadow">
        <button onclick="closeCalOverview()" class="p-2 -ml-2 rounded-lg hover:bg-white/10 transition-colors">
            <i data-feather="arrow-left" class="w-5 h-5"></i>
        </button>
        <span class="text-sm font-semibold">Calendario</span>
        <button type="button" onclick="goToToday()" id="cal-today-btn"
            class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-white/15 text-white active:bg-white/25">
            Oggi
        </button>
    </div>
    <!-- View switcher -->
    <div class="flex-shrink-0 flex bg-white border-b border-gray-100">
        <button id="cal-view-year" onclick="setCalView('year')"
            class="flex-1 text-xs font-semibold py-2.5 text-gray-400 border-b-2 border-transparent">Anno</button>
        <button id="cal-view-month" onclick="setCalView('month')"
            class="flex-1 text-xs font-semibold py-2.5 text-brand-600 border-b-2 border-brand-600">Mese</button>
    </div>
    <div id="cal-ov-loading" class="flex-1 flex items-center justify-center">
        <div class="space-y-3 w-full px-4">
            <div class="skeleton h-8 rounded-xl"></div>
            <div class="skeleton h-64 rounded-2xl"></div>
            <div class="skeleton h-24 rounded-2xl"></div>
        </div>
    </div>
    <div id="cal-ov-content" class="hidden flex-1 overflow-y-auto pb-8" style="padding-bottom: env(safe-area-inset-bottom)">
        <!-- Month view -->
        <div id="cal-ov-month-panel">
            <div class="flex items-center justify-between px-4 py-3 bg-white border-b border-gray-100">
                <button onclick="navCalOv(-1)" class="p-2 rounded-xl text-gray-500 active:bg-gray-100">
                    <i data-feather="chevron-left" class="w-5 h-5"></i>
                </button>
                <button onclick="setCalView('year')" id="cal-ov-month-label" class="text-sm font-bold text-gray-800 active:opacity-70"></button>
                <button onclick="navCalOv(1)" class="p-2 rounded-xl text-gray-500 active:bg-gray-100">
                    <i data-feather="chevron-right" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="cal-weekdays">
                <span>LUN</span><span>MAR</span><span>MER</span><span>GIO</span><span>VEN</span><span>SAB</span><span>DOM</span>
            </div>
            <div id="cal-ov-grid"></div>
            <div class="flex items-center justify-center flex-wrap gap-x-4 gap-y-1 px-4 py-3 bg-white border-t border-gray-100">
                <span class="flex items-center space-x-1 text-[10px] text-gray-500"><span class="w-2 h-2 rounded-full bg-sky-400 inline-block"></span><span>Evento</span></span>
                <span class="flex items-center space-x-1 text-[10px] text-gray-500"><span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span><span>Ordine</span></span>
                <span class="flex items-center space-x-1 text-[10px] text-gray-500"><span class="w-2 h-2 rounded bg-purple-400 inline-block"></span><span>Ticket</span></span>
            </div>
        </div>
        <!-- Year view -->
        <div id="cal-ov-year-panel" class="hidden px-4 py-4">
            <div class="flex items-center justify-between mb-4">
                <button onclick="navCalYear(-1)" class="p-2 rounded-xl text-gray-500 active:bg-gray-100 bg-white border border-gray-100">
                    <i data-feather="chevron-left" class="w-5 h-5"></i>
                </button>
                <span id="cal-ov-year-label" class="text-lg font-bold text-gray-800"></span>
                <button onclick="navCalYear(1)" class="p-2 rounded-xl text-gray-500 active:bg-gray-100 bg-white border border-gray-100">
                    <i data-feather="chevron-right" class="w-5 h-5"></i>
                </button>
            </div>
            <div id="cal-ov-year-grid" class="grid grid-cols-3 gap-3"></div>
        </div>
    </div>
</div>

<script>
feather.replace();

const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const CURRENT_USER_ID = {{ $userId ?? 'null' }};
const CURRENT_USER_NAME = @json($userName);

// ── Loaded flags (lazy loading) ──────────────────────────────────────────────
const loaded = { agenda: false, invoices: false, recoveries: false };

function formatEuro(amount) {
    const n = Number(amount);
    if (!Number.isFinite(n)) return '—';
    return n.toLocaleString('it-IT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
}

function invoiceCanPayToTechnician(inv) {
    return inv?.can_pay_to_technician === true;
}

function invoiceHasPendingTechnicianCash(inv) {
    return inv?.has_pending_technician_cash === true;
}

function invoiceNeedsPaymentModal(inv) {
    return !inv?.delivered_at
        && invoiceCanPayToTechnician(inv)
        && !invoiceHasPendingTechnicianCash(inv);
}

function invoiceNeedsLatePayment(inv) {
    return !!inv?.delivered_at
        && invoiceCanPayToTechnician(inv)
        && !invoiceHasPendingTechnicianCash(inv);
}

function invoiceCanUndoDelivery(inv) {
    return !!inv?.delivered_at && !invoiceHasPendingTechnicianCash(inv);
}

function getInvoiceBalanceDueLabel(inv) {
    if (inv?.balance_due_label) return inv.balance_due_label;
    if (inv?.balance_due != null) return formatEuro(inv.balance_due);
    return '—';
}

function renderInvoicePaymentBanner(inv) {
    if (invoiceHasPendingTechnicianCash(inv)) {
        return `<div class="mb-2 flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
            <i data-feather="dollar-sign" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5"></i>
            <p class="text-xs font-bold text-amber-800">Incasso già in cassa tecnico</p>
        </div>`;
    }
    if (invoiceCanPayToTechnician(inv)) {
        return `<div class="mb-2 flex items-start gap-2 bg-red-50 border border-red-200 rounded-xl px-3 py-2">
            <i data-feather="alert-circle" class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5"></i>
            <p class="text-xs font-bold text-red-800">Da saldare: ${esc(getInvoiceBalanceDueLabel(inv))}</p>
        </div>`;
    }
    return '';
}

function updateMainHeaderOffset() {
    const header = document.getElementById('main-header');
    const main = document.getElementById('main-content');
    if (!header || !main) return;
    main.style.paddingTop = `${header.offsetHeight}px`;
    const top = `calc(${header.offsetHeight}px)`;
    ['search-panel', 'cal-overview'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.top = top;
    });
}

async function loadTechnicianCashSummary() {
    const bar = document.getElementById('tech-cash-bar');
    const totalEl = document.getElementById('tech-cash-total');
    if (!bar || !totalEl) return;

    try {
        const res = await fetch('/api/technician/cash/summary', { headers: { 'X-CSRF-TOKEN': CSRF } });
        if (res.status === 401) return;
        if (!res.ok) return;

        const json = await res.json();
        const data = json.data ?? json;
        totalEl.textContent = formatEuro(data.total ?? 0);
        bar.classList.remove('hidden');
        updateMainHeaderOffset();
        feather.replace();
    } catch (e) {
        // Cassa opzionale: non bloccare l'app se l'endpoint non risponde
    }
}

// ── Agenda cache (API già filtra per tecnico loggato) ─────────────────────────
const TICKET_LEVEL_ORDER = { high: 0, normal: 1, low: 2 };
const _agendaCache = { calendar: [], activities: [] };

function toLocalIsoDate(d) {
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

function todayIso() {
    return toLocalIsoDate(new Date());
}

function dateInRange(date, start, end) {
    if (!start) return false;
    const e = end || start;
    return start <= date && date <= e;
}

function sortKeyTime(time) {
    return time ? time.slice(0, 8) : '99:99:99';
}

function updateAgendaDateLabel() {
    const date = document.getElementById('agenda-date').value;
    const el = document.getElementById('agenda-date-label');
    if (!el || !date) return;
    const d = new Date(date + 'T12:00:00');
    const label = d.toLocaleDateString('it-IT', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    el.textContent = date === todayIso() ? `Oggi — ${label}` : label;
}

async function fetchAgendaData() {
    const [calRes, actRes] = await Promise.all([
        fetch('/api/technician/calendar-events', { headers: { 'X-CSRF-TOKEN': CSRF } }),
        fetch('/api/technician/cart-activities',  { headers: { 'X-CSRF-TOKEN': CSRF } }),
    ]);
    if ([calRes, actRes].some(r => r.status === 401)) { showSessionExpired(); return false; }
    if (![calRes, actRes].every(r => r.ok)) throw new Error('fetch failed');

    const [calJson, actJson] = await Promise.all([calRes.json(), actRes.json()]);
    _agendaCache.calendar   = calJson.data ?? [];
    _agendaCache.activities = actJson.data ?? [];
    return true;
}

function getDailyItems(date) {
    const orders = _agendaCache.activities
        .filter(a => a.event_at === date)
        .slice()
        .sort((a, b) => sortKeyTime(a.event_time).localeCompare(sortKeyTime(b.event_time)));

    const events = _agendaCache.calendar
        .filter(e => e.event_type !== 'ticket' && dateInRange(date, e.start_date, e.end_date ?? e.start_date))
        .slice()
        .sort((a, b) => sortKeyTime(a.start_time).localeCompare(sortKeyTime(b.start_time)));

    // Ticket solo se programmati in calendario per questa data (come il gestionale)
    const tickets = _agendaCache.calendar
        .filter(e => e.event_type === 'ticket' && dateInRange(date, e.start_date, e.end_date ?? e.start_date))
        .slice()
        .sort((a, b) => {
            const la = TICKET_LEVEL_ORDER[a.ticket_level ?? a.level] ?? 99;
            const lb = TICKET_LEVEL_ORDER[b.ticket_level ?? b.level] ?? 99;
            return la - lb || sortKeyTime(a.start_time).localeCompare(sortKeyTime(b.start_time));
        });

    return { orders, events, tickets };
}

function countItemsOnDate(date) {
    const { orders, events, tickets } = getDailyItems(date);
    return orders.length + events.length + tickets.length;
}

// ── Search ────────────────────────────────────────────────────────────────────
let _searchTimer = null;

function normalizeSearchText(value) {
    return String(value ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '');
}

function buildSearchHaystack(obj) {
    const parts = [];
    const walk = (v) => {
        if (v == null || typeof v === 'boolean') return;
        if (typeof v === 'string' || typeof v === 'number') parts.push(normalizeSearchText(v));
        else if (Array.isArray(v)) v.forEach(walk);
        else if (typeof v === 'object') Object.values(v).forEach(walk);
    };
    walk(obj);
    return parts.join(' ');
}

function collectAllSearchItems() {
    const items = [];

    for (const ev of _agendaCache.calendar) {
        if (!ev.start_date) continue;
        const isTicket = ev.event_type === 'ticket';
        const isSegnalazione = ev.event_type === 'segnalazione';
        items.push({
            type: 'calendar',
            id: ev.id,
            date: ev.start_date,
            startTime: ev.start_time,
            endTime: ev.end_time,
            title: ev.title || (isTicket ? 'Ticket' : isSegnalazione ? 'Segnalazione' : 'Evento'),
            subtitle: [ev.customer, ev.full_address, ev.description, ev.department].filter(Boolean).join(' · '),
            color: ev.color || (isTicket ? '#9c27b0' : isSegnalazione ? '#f97316' : '#0284c7'),
            haystack: buildSearchHaystack(ev),
        });
    }

    for (const act of _agendaCache.activities) {
        if (!act.event_at) continue;
        items.push({
            type: 'activity',
            id: act.id,
            date: act.event_at,
            startTime: act.event_time,
            endTime: null,
            title: act.customer || 'Ordine',
            subtitle: [act.full_address, act.address_notes, act.note, act.notes, act.offer?.name, act.offer?.description].filter(Boolean).join(' · '),
            color: '#ea8600',
            haystack: buildSearchHaystack(act),
        });
    }

    return items.sort((a, b) => {
        const byDate = a.date.localeCompare(b.date);
        if (byDate !== 0) return byDate;
        return sortKeyTime(a.startTime).localeCompare(sortKeyTime(b.startTime));
    });
}

function formatSearchDateParts(dateStr) {
    const d = new Date(dateStr + 'T12:00:00');
    const month = d.toLocaleDateString('it-IT', { month: 'short' }).replace('.', '').toUpperCase();
    const weekday = d.toLocaleDateString('it-IT', { weekday: 'short' }).replace('.', '').toUpperCase();
    return {
        day: d.getDate(),
        monthYear: `${month} ${d.getFullYear()}`,
        weekday,
        isToday: dateStr === todayIso(),
    };
}

function formatSearchTimeRange(start, end) {
    if (!start) return '—';
    const from = start.slice(0, 5);
    if (!end) return from;
    return `${from} – ${end.slice(0, 5)}`;
}

function renderSearchResults(query) {
    const q = normalizeSearchText(query.trim());
    const hint = document.getElementById('search-hint');
    const empty = document.getElementById('search-empty');
    const list = document.getElementById('search-results');

    if (!q) {
        hint.classList.remove('hidden');
        empty.classList.add('hidden');
        list.classList.add('hidden');
        list.innerHTML = '';
        return;
    }

    const tokens = q.split(/\s+/).filter(Boolean);
    const matches = collectAllSearchItems().filter(item =>
        tokens.every(token => item.haystack.includes(token))
    );

    hint.classList.add('hidden');

    if (!matches.length) {
        empty.classList.remove('hidden');
        list.classList.add('hidden');
        list.innerHTML = '';
        return;
    }

    empty.classList.add('hidden');
    list.classList.remove('hidden');
    list.innerHTML = matches.map(item => {
        const dp = formatSearchDateParts(item.date);
        const dayClass = dp.isToday ? 'search-row-date-day is-today' : 'search-row-date-day';
        return `
        <button type="button" onclick="openSearchResult('${item.type}', ${item.id}, '${item.date}')"
            class="w-full flex items-start gap-3 px-4 py-3 border-b border-gray-100 text-left active:bg-gray-50 hover:bg-gray-50/80 transition-colors">
            <div class="flex-shrink-0 w-[3.25rem] pt-0.5 text-center">
                <div class="${dayClass}">${dp.day}</div>
                <div class="text-[10px] text-gray-500 uppercase leading-tight mt-1">${dp.monthYear}</div>
                <div class="text-[10px] text-gray-400 uppercase">${dp.weekday}</div>
            </div>
            <div class="flex flex-1 min-w-0 gap-2 pt-1">
                <div class="search-row-time flex items-start gap-1.5 flex-shrink-0">
                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 mt-1" style="background:${item.color}"></span>
                    <span class="text-xs text-gray-600 leading-snug">${formatSearchTimeRange(item.startTime, item.endTime)}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 leading-snug">${esc(item.title)}</p>
                    ${item.subtitle ? `<p class="text-xs text-gray-500 leading-snug mt-0.5 line-clamp-2">${esc(item.subtitle)}</p>` : ''}
                </div>
            </div>
        </button>`;
    }).join('');

    feather.replace();
}

function onSearchInput() {
    const input = document.getElementById('search-input');
    const clearBtn = document.getElementById('search-clear');
    clearBtn.classList.toggle('hidden', !input.value);
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(() => renderSearchResults(input.value), 120);
}

function clearSearchInput() {
    const input = document.getElementById('search-input');
    input.value = '';
    document.getElementById('search-clear').classList.add('hidden');
    renderSearchResults('');
    input.focus();
}

async function openSearchPanel() {
    document.getElementById('search-panel').classList.remove('hidden');
    document.getElementById('search-hint').classList.remove('hidden');
    document.getElementById('search-empty').classList.add('hidden');
    document.getElementById('search-results').classList.add('hidden');
    document.getElementById('search-results').innerHTML = '';
    document.getElementById('search-loading').classList.add('hidden');

    const input = document.getElementById('search-input');
    input.value = '';
    document.getElementById('search-clear').classList.add('hidden');

    if (!_agendaCache.calendar.length && !_agendaCache.activities.length) {
        document.getElementById('search-hint').classList.add('hidden');
        document.getElementById('search-loading').classList.remove('hidden');
        try {
            const ok = await fetchAgendaData();
            if (!ok) { closeSearchPanel(); return; }
        } catch (_) {
            closeSearchPanel();
            return;
        }
        document.getElementById('search-loading').classList.add('hidden');
        document.getElementById('search-hint').classList.remove('hidden');
    }

    feather.replace();
    setTimeout(() => input.focus(), 80);
}

function closeSearchPanel() {
    document.getElementById('search-panel').classList.add('hidden');
    clearTimeout(_searchTimer);
}

function openSearchResult(type, id, dateStr) {
    closeSearchPanel();
    switchTab('agenda');
    document.getElementById('agenda-date').value = dateStr;
    updateAgendaDateLabel();
    updateTodayButton();
    if (_agendaCache.calendar.length || _agendaCache.activities.length) {
        renderAgendaList();
    }
    openActivityDetail(type, id);
}

// ── Session expired ──────────────────────────────────────────────────────────
function showSessionExpired() {
    document.getElementById('session-expired').classList.remove('hidden');
}

// ── Tab switching ─────────────────────────────────────────────────────────────
const tabs     = ['agenda', 'invoices', 'recoveries'];
const sections = { agenda: 'section-agenda', invoices: 'section-invoices', recoveries: 'section-recoveries' };

function getActiveTab() {
    for (const t of tabs) {
        if (!document.getElementById(sections[t]).classList.contains('hidden')) return t;
    }
    return 'agenda';
}

async function refreshCurrentTab() {
    await loadTechnicianCashSummary();
    const tab = getActiveTab();
    if (tab === 'invoices') {
        await openInvoicesSection();
        return;
    }
    if (tab === 'recoveries') {
        await openRecoveriesSection();
        return;
    }

    const calOpen = !document.getElementById('cal-overview').classList.contains('hidden');
    const calLoading = document.getElementById('cal-ov-loading');
    const calContent = document.getElementById('cal-ov-content');
    if (calOpen) {
        calLoading.classList.remove('hidden');
        calContent.classList.add('hidden');
    }

    try {
        await loadAgenda(true);
        if (calOpen) _renderCalView();
    } finally {
        if (calOpen) {
            calLoading.classList.add('hidden');
            calContent.classList.remove('hidden');
            feather.replace();
        }
    }
}

function switchTab(name) {
    tabs.forEach(t => {
        document.getElementById(sections[t]).classList.toggle('hidden', t !== name);
    });
    updateSideMenuActive(name);

    if (name === 'agenda' && !loaded.agenda) {
        loadAgenda();
    } else if (name === 'invoices') {
        openInvoicesSection();
    } else if (name === 'recoveries') {
        openRecoveriesSection();
    }
}

function updateSideMenuActive(name) {
    const invBtn = document.getElementById('side-menu-invoices');
    const recBtn = document.getElementById('side-menu-recoveries');
    if (invBtn) invBtn.classList.toggle('is-active', name === 'invoices');
    if (recBtn) recBtn.classList.toggle('is-active', name === 'recoveries');
}

function openSideMenu() {
    document.getElementById('side-menu-overlay').classList.add('is-open');
    document.getElementById('side-menu').classList.add('is-open');
    document.getElementById('side-menu-overlay').setAttribute('aria-hidden', 'false');
    document.getElementById('side-menu').setAttribute('aria-hidden', 'false');
    updateSideMenuActive(getActiveTab());
    feather.replace();
}

function closeSideMenu() {
    document.getElementById('side-menu-overlay').classList.remove('is-open');
    document.getElementById('side-menu').classList.remove('is-open');
    document.getElementById('side-menu-overlay').setAttribute('aria-hidden', 'true');
    document.getElementById('side-menu').setAttribute('aria-hidden', 'true');
}

function menuNavigateInvoices() {
    closeSideMenu();
    switchTab('invoices');
}

function menuNavigateRecoveries() {
    closeSideMenu();
    switchTab('recoveries');
}

function menuOpenSegnala() {
    closeSideMenu();
    openReportModal();
}

function menuOpenCalOverview() {
    closeSideMenu();
    openCalOverview();
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function showState(prefix, state) {
    ['loading', 'error', 'empty', 'search-empty', 'list'].forEach(s => {
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

function resolveDeliveredByName(inv) {
    if (inv.delivered_by_name) return inv.delivered_by_name;
    if (typeof inv.delivered_by === 'string' && inv.delivered_by.trim() && !/^\d+$/.test(inv.delivered_by.trim())) {
        return inv.delivered_by.trim();
    }
    if (inv.delivered_by && CURRENT_USER_ID && inv.delivered_by == CURRENT_USER_ID && CURRENT_USER_NAME) {
        return CURRENT_USER_NAME;
    }
    // Solo il nome subito prima di " durante la consegna:" (mai tutto il campo notes)
    if (inv.notes && inv.notes.includes(' durante la consegna')) {
        const m = inv.notes.match(/([A-ZÀ-ÿ][\p{L}'’-]+(?: [A-ZÀ-ÿ][\p{L}'’-]+)+) durante la consegna:/u);
        if (m) return m[1].trim();
    }
    return '';
}

function formatDeliveredLabel(inv) {
    if (!inv.delivered_at) return '';
    const d = new Date(inv.delivered_at);
    const date = d.toLocaleDateString('it-IT', { day: 'numeric', month: 'long', year: 'numeric' });
    const time = d.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
    const who = resolveDeliveredByName(inv);
    return who
        ? `Consegnata da ${who} il ${date} alle ${time}`
        : `Consegnata il ${date} alle ${time}`;
}

function cleanAddressValue(value) {
    if (!value) return '';
    const trimmed = String(value).trim().replace(/\s+/g, ' ');
    if (!trimmed || trimmed === ',' || /^[\s,]+$/.test(trimmed)) return '';
    return trimmed;
}

function getInvoicePhone(inv) {
    return cleanAddressValue(inv.phone || inv.customer_phone || inv.mobile || inv.telefono || '');
}

function getInvoiceDisplayAddress(inv) {
    if (inv.display_address) {
        return {
            label: inv.display_address_label || 'Indirizzo',
            address: cleanAddressValue(inv.display_address),
        };
    }
    const delivery = cleanAddressValue(inv.delivery_address || inv.invoice_delivery_address);
    if (delivery) return { label: 'Indirizzo consegna', address: delivery };
    const install = cleanAddressValue(inv.installation_address || inv.install_address);
    if (install) return { label: 'Indirizzo installazione', address: install };
    const customer = cleanAddressValue(
        inv.customer_address || inv.billing_address || inv.registry_address || inv.anagrafica_address || inv.user_address
    );
    if (customer) return { label: 'Indirizzo cliente', address: customer };
    const full = cleanAddressValue(inv.full_address);
    if (full) return { label: 'Indirizzo', address: full };
    return null;
}

function renderInvoiceContactBlock(inv) {
    const phone = getInvoicePhone(inv);
    const addr  = getInvoiceDisplayAddress(inv);
    let html = '';
    if (phone) {
        const tel = phone.replace(/[^\d+]/g, '');
        html += `<a href="tel:${tel}" class="flex items-start gap-1.5 text-xs text-brand-600 font-medium mb-2.5 active:opacity-70">
            <i data-feather="phone" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5"></i>
            <span class="underline underline-offset-2">${esc(phone)}</span>
        </a>`;
    }
    if (addr) {
        html += `<div class="flex items-start gap-1.5 text-xs text-gray-600 mb-2">
            <i data-feather="map-pin" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-gray-400"></i>
            <p class="leading-snug"><span class="font-semibold text-gray-500">${addr.label}:</span> ${esc(addr.address)}</p>
        </div>`;
    }
    return html;
}

function buildMapNavUrls(coords, prevCoords) {
    if (!coords) return { directionsUrl: null, wazeUrl: null };
    const [lat, lng] = coords.split(',').map(s => s.trim());
    if (!lat || !lng) return { directionsUrl: null, wazeUrl: null };

    const wazeUrl = `waze://?ll=${lat},${lng}&navigate=yes`;
    const dest = encodeURIComponent(coords);
    const origin = prevCoords ? encodeURIComponent(prevCoords) : null;
    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    let directionsUrl;
    if (isIos) {
        directionsUrl = origin
            ? `comgooglemaps://?saddr=${origin}&daddr=${dest}&directionsmode=driving`
            : `comgooglemaps://?daddr=${dest}&directionsmode=driving`;
    } else if (navigator.maxTouchPoints > 0) {
        const navParams = origin
            ? `maps?saddr=${origin}&daddr=${dest}&dirflg=d`
            : `maps?daddr=${dest}&dirflg=d`;
        directionsUrl = `intent://maps.google.com/${navParams}` +
            `#Intent;scheme=https;package=com.google.android.apps.maps;end`;
    } else {
        directionsUrl = origin
            ? `https://maps.google.com/maps?saddr=${origin}&daddr=${dest}&dirflg=d`
            : `https://maps.google.com/maps?daddr=${dest}&dirflg=d`;
    }
    return { directionsUrl, wazeUrl };
}

function renderMapLinksHtml(coords, prevCoords, addressFallback, apiUrls = null) {
    let directionsUrl = null;
    let wazeUrl = null;
    let navEncoded = coords ? encodeURIComponent(coords) : '';

    if (apiUrls?.maps_url) directionsUrl = apiUrls.maps_url;
    if (apiUrls?.waze_url) wazeUrl = apiUrls.waze_url;

    if (!directionsUrl || !wazeUrl) {
        if (coords) {
            const built = buildMapNavUrls(coords, prevCoords);
            if (!directionsUrl) directionsUrl = built.directionsUrl;
            if (!wazeUrl) wazeUrl = built.wazeUrl;
        } else if (addressFallback) {
            const q = encodeURIComponent(addressFallback);
            navEncoded = navEncoded || q;
            if (!directionsUrl) directionsUrl = `https://www.google.com/maps/search/?api=1&query=${q}`;
            if (!wazeUrl) wazeUrl = `https://waze.com/ul?q=${q}&navigate=yes`;
        }
    } else if (!navEncoded && addressFallback) {
        navEncoded = encodeURIComponent(addressFallback);
    }

    if (!directionsUrl && !wazeUrl) return '';
    return `<div class="mb-3 flex items-center gap-4">
        ${directionsUrl ? `
        <a href="${directionsUrl}" target="_blank" rel="noopener"
            onclick="handleNavigation(event, '${directionsUrl}', '${navEncoded}')"
            class="flex items-center space-x-1.5 text-xs text-brand-600 font-medium active:opacity-70">
            <i data-feather="navigation" class="w-3.5 h-3.5 flex-shrink-0"></i>
            <span class="underline underline-offset-2">Google Maps</span>
        </a>` : ''}
        ${wazeUrl ? `
        <a href="${wazeUrl}" target="_blank" rel="noopener"
            ${wazeUrl.startsWith('waze://') ? `onclick="handleWaze(event, '${wazeUrl}')"` : ''}
            class="flex items-center space-x-1.5 text-xs text-blue-500 font-medium active:opacity-70">
            <i data-feather="navigation" class="w-3.5 h-3.5 flex-shrink-0"></i>
            <span class="underline underline-offset-2">Waze</span>
        </a>` : ''}
    </div>`;
}

function renderPhoneLinkHtml(item) {
    const phone = cleanAddressValue(item?.phone || item?.customer_phone || item?.mobile || item?.telefono || '');
    if (!phone) return '';
    const telHref = cleanAddressValue(item?.phone_tel)
        || `tel:${phone.replace(/[^\d+]/g, '')}`;
    return `<a href="${esc(telHref)}" class="flex items-start gap-1.5 text-xs text-brand-600 font-medium mb-2.5 active:opacity-70">
        <i data-feather="phone" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5"></i>
        <span class="underline underline-offset-2">${esc(phone)}</span>
    </a>`;
}

function renderActivityContactBlock(item) {
    const phoneHtml = renderPhoneLinkHtml(item);
    const addr = getRecoveryDisplayAddress(item);
    let html = phoneHtml;
    if (addr) {
        html += `<div class="flex items-start gap-1.5 text-xs text-gray-600 mb-2">
            <i data-feather="map-pin" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-gray-400"></i>
            <p class="leading-snug"><span class="font-semibold text-gray-500">${esc(addr.label)}:</span> ${esc(addr.address)}</p>
        </div>`;
    }
    html += renderActivityAddressNotesBlock(item);
    return html;
}

function normalizeNotesText(value) {
    if (value == null || value === '') return '';
    if (typeof value === 'string') {
        const cleaned = cleanAddressValue(value);
        return cleaned || value.trim();
    }
    if (Array.isArray(value)) {
        return value.map(entry => {
            if (typeof entry === 'string') return entry.trim();
            if (entry && typeof entry === 'object') {
                return String(entry.body ?? entry.note ?? entry.text ?? entry.message ?? '').trim();
            }
            return '';
        }).filter(Boolean).join('\n');
    }
    if (typeof value === 'object') {
        return String(value.body ?? value.note ?? value.text ?? value.message ?? '').trim();
    }
    return String(value).trim();
}

function getActivityAddressNotes(item) {
    return normalizeNotesText(item?.address_notes)
        || normalizeNotesText(item?.installation_notes)
        || normalizeNotesText(item?.address_note)
        || normalizeNotesText(item?.backoffice_notes)
        || normalizeNotesText(item?.note);
}

function renderActivityAddressNotesBlock(item) {
    const text = getActivityAddressNotes(item);
    if (!text) return '';
    return `<div class="mb-2 rounded-xl border border-amber-100 bg-amber-50 px-3 py-2">
        <p class="text-[10px] font-bold text-amber-700 uppercase tracking-wide mb-1">Note backoffice</p>
        <p class="text-xs text-amber-900 leading-snug whitespace-pre-wrap">${esc(text)}</p>
    </div>`;
}

function renderActivityMapLinks(item, prevCoords = null) {
    const address = getRecoveryMapAddress(item);
    const coords = cleanAddressValue(item?.coordinates) || null;
    if (!coords && !address && !item?.maps_url && !item?.waze_url) return '';
    return renderMapLinksHtml(coords, prevCoords, address, {
        maps_url: item?.maps_url || null,
        waze_url: item?.waze_url || null,
    });
}

function getRecoveryMapAddress(item) {
    const addr = getRecoveryDisplayAddress(item);
    return cleanAddressValue(item.full_address) || addr?.address || '';
}

function renderRecoveryContactsPreview(contacts) {
    const list = Array.isArray(contacts) ? contacts : [];
    if (!list.length) return '';
    const preview = list.slice(0, 3);
    return `<div class="mt-3 pt-3 border-t border-gray-100">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-2">Contatti registrati</p>
        <div class="space-y-2">
            ${preview.map(c => `
            <div class="bg-gray-50 rounded-lg px-3 py-2">
                <p class="text-[10px] text-gray-400">${esc(c.contacted_at_label || '—')}${c.user ? ` · ${esc(c.user)}` : ''}</p>
                <p class="text-xs text-gray-700 mt-0.5 leading-snug whitespace-pre-wrap">${esc(c.note || '')}</p>
            </div>`).join('')}
            ${list.length > 3 ? `<p class="text-[10px] text-gray-400">+ altri ${list.length - 3} contatti</p>` : ''}
        </div>
    </div>`;
}

function recoveryHasUnpaidInvoices(item) {
    if (!item) return false;
    if (item.has_unpaid_invoices === true || item.has_unpaid_invoice === true) return true;
    if (Number(item.unpaid_invoices_count) > 0) return true;
    return Array.isArray(item.unpaid_invoices) && item.unpaid_invoices.length > 0;
}

function getRecoveryUnpaidSummary(item) {
    if (item?.unpaid_invoices_summary) return item.unpaid_invoices_summary;
    const list = item?.unpaid_invoices || [];
    if (list.length) {
        return list.map(inv => inv.label || inv.description || inv.invoice_name || inv.name).filter(Boolean).join(' · ');
    }
    const count = Number(item?.unpaid_invoices_count || 0);
    if (count === 1) return '1 fattura da saldare';
    if (count > 1) return `${count} fatture da saldare`;
    return 'Fatture da saldare';
}

function getRecoveryUnpaidInvoices(item) {
    return (item?.unpaid_invoices || []).filter(inv => inv && inv.id != null);
}

function getRecoveryUnpaidInvoiceOptions(item) {
    return getRecoveryUnpaidInvoices(item).map(inv => ({
        id: inv.id,
        label: inv.label || inv.description || inv.invoice_name || inv.invoice_code || inv.name || `Fattura #${inv.id}`,
    }));
}

function populateRecoveryPaymentInvoices(item) {
    const wrap = document.getElementById('recovery-payment-invoice-wrap');
    const select = document.getElementById('recovery-payment-invoice-id');
    if (!wrap || !select) return null;

    const options = getRecoveryUnpaidInvoiceOptions(item);
    if (options.length <= 1) {
        wrap.classList.add('hidden');
        select.innerHTML = '';
        return options[0]?.id ?? null;
    }

    select.innerHTML = options.map(opt =>
        `<option value="${opt.id}">${esc(opt.label)}</option>`
    ).join('');
    wrap.classList.remove('hidden');
    return options[0]?.id ?? null;
}

function getSelectedRecoveryInvoiceId() {
    const select = document.getElementById('recovery-payment-invoice-id');
    const wrap = document.getElementById('recovery-payment-invoice-wrap');
    if (select && !wrap.classList.contains('hidden') && select.value) {
        return parseInt(select.value, 10);
    }
    return null;
}

function recoveryInvoiceStatusKnown(item) {
    return item?.invoice_status_known === true;
}

function renderRecoveryInvoiceStatusBanner(item) {
    if (isRecoveryCompleted(item)) return '';

    if (recoveryHasUnpaidInvoices(item)) {
        return `<div class="mb-2 flex items-start gap-2 bg-red-50 border border-red-200 rounded-xl px-3 py-2">
            <i data-feather="alert-circle" class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="text-xs font-bold text-red-800">Fatture da saldare</p>
                <p class="text-[11px] text-red-700 leading-snug">${esc(getRecoveryUnpaidSummary(item))}</p>
            </div>
        </div>`;
    }

    if (recoveryInvoiceStatusKnown(item)) {
        return `<div class="mb-2 flex items-start gap-2 bg-green-50 border border-green-200 rounded-xl px-3 py-2">
            <i data-feather="check-circle" class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5"></i>
            <p class="text-xs font-bold text-green-800">Nessuna fattura da saldare</p>
        </div>`;
    }

    return '';
}

function getRecoveryItem(recoveryId) {
    return allRecoveries.find(r => r.id == recoveryId)
        || (_recoverySheetData?.id == recoveryId ? _recoverySheetData : null);
}

function renderRecoveryCardActions(item) {
    const id = item.id;
    return `
    <div class="mt-3 pt-3 border-t border-gray-100 space-y-2">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Azioni tecnico</p>
        <textarea id="recovery-contact-note-${id}" rows="2" placeholder="Nota contatto (obbligatoria per registrare)…"
            class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2 resize-none focus:outline-none focus:border-brand-400 bg-gray-50"></textarea>
        <button type="button" onclick="submitRecoveryContact(${id}, this)"
            class="w-full flex items-center justify-center gap-1.5 bg-brand-600 active:bg-brand-700 text-white text-sm font-semibold py-3 rounded-xl shadow-sm">
            <i data-feather="phone-call" class="w-4 h-4"></i>
            <span>Registra contatto</span>
        </button>
        <textarea id="recovery-complete-note-${id}" rows="1" placeholder="Nota recupero (opzionale)…"
            class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2 resize-none focus:outline-none focus:border-brand-400 bg-gray-50"></textarea>
        <button type="button" onclick="submitRecoveryComplete(${id}, this)"
            class="w-full flex items-center justify-center gap-1.5 bg-green-600 active:bg-green-700 text-white text-sm font-semibold py-3 rounded-xl shadow-sm">
            <i data-feather="check-circle" class="w-4 h-4"></i>
            <span>Recupero completato</span>
        </button>
    </div>`;
}

function isRecoveryCompleted(item) {
    if (!item) return false;
    const status = String(item.status || '').toLowerCase();
    return ['completed', 'customer_reentry', 'closed', 'done'].includes(status);
}

function getRecoveryPhone(item) {
    return cleanAddressValue(item.phone || item.customer_phone || item.mobile || item.telefono || '');
}

function getRecoveryDisplayAddress(item) {
    if (item.display_address) {
        return {
            label: item.display_address_label || 'Indirizzo',
            address: cleanAddressValue(item.display_address),
        };
    }
    const install = cleanAddressValue(item.installation_address);
    if (install) return { label: 'Indirizzo installazione', address: install };
    const customer = cleanAddressValue(item.customer_address);
    if (customer) return { label: 'Indirizzo cliente', address: customer };
    const full = cleanAddressValue(item.full_address);
    if (full) return { label: 'Indirizzo', address: full };
    return null;
}

function renderRecoveryContactBlock(item) {
    const phone = getRecoveryPhone(item);
    const addr  = getRecoveryDisplayAddress(item);
    let html = '';
    if (phone) {
        const tel = phone.replace(/[^\d+]/g, '');
        html += `<a href="tel:${tel}" class="flex items-start gap-1.5 text-xs text-brand-600 font-medium mb-2.5 active:opacity-70">
            <i data-feather="phone" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5"></i>
            <span class="underline underline-offset-2">${esc(phone)}</span>
        </a>`;
    }
    if (addr) {
        html += `<div class="flex items-start gap-1.5 text-xs text-gray-600 mb-2">
            <i data-feather="map-pin" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-gray-400"></i>
            <p class="leading-snug"><span class="font-semibold text-gray-500">${addr.label}:</span> ${esc(addr.address)}</p>
        </div>`;
    }
    return html;
}

function renderRecoveryContactsTimeline(contacts) {
    const list = Array.isArray(contacts) ? contacts : [];
    if (!list.length) {
        return `<p class="text-xs text-gray-400 italic">Nessun contatto backoffice registrato.</p>`;
    }
    return `<div class="space-y-3">
        ${list.map(c => `
        <div class="border-l-2 border-brand-200 pl-3 py-0.5">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">${esc(c.contacted_at_label || '—')}</p>
            ${c.user ? `<p class="text-xs font-semibold text-gray-700 mt-0.5">${esc(c.user)}</p>` : ''}
            <p class="text-xs text-gray-600 mt-1 leading-snug whitespace-pre-wrap">${esc(c.note || '')}</p>
        </div>`).join('')}
    </div>`;
}

function recoveryStatusBadge(item) {
    const label = item.status_label || item.status || '—';
    const status = String(item.status || '').toLowerCase();
    const cls = {
        open: 'bg-sky-100 text-sky-700',
        pending: 'bg-yellow-100 text-yellow-700',
        in_progress: 'bg-orange-100 text-orange-700',
        completed: 'bg-green-100 text-green-700',
        customer_reentry: 'bg-purple-100 text-purple-700',
        closed: 'bg-gray-100 text-gray-500',
    }[status] || 'bg-gray-100 text-gray-600';
    return `<span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${cls}">${esc(label)}</span>`;
}

function levelBadge(level) {
    const map = { high: 'bg-red-100 text-red-700', normal: 'bg-blue-100 text-blue-700', low: 'bg-gray-100 text-gray-600' };
    const labels = { high: 'Alta', normal: 'Normale', low: 'Bassa' };
    return `<span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${map[level] ?? 'bg-gray-100 text-gray-600'}">${labels[level] ?? level}</span>`;
}

function statusBadge(status, labelOverride) {
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
    const text = labelOverride ?? labels[status] ?? status;
    return `<span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${map[status] ?? 'bg-gray-100 text-gray-500'}">${esc(text)}</span>`;
}

// ── Notifiche push (OneSignal) ────────────────────────────────────────────────
// Su iOS PWA la campanella floating di OneSignal non appare in modo affidabile,
// quindi gestiamo lo stato della sottoscrizione dal menu laterale.
async function handleNotifToggle() {
    if (!window.OneSignal?.User?.PushSubscription) return;
    try {
        const optedIn = OneSignal.User.PushSubscription.optedIn;
        if (optedIn) {
            await OneSignal.User.PushSubscription.optOut();
        } else {
            await OneSignal.User.PushSubscription.optIn();
        }
    } catch (e) {
        console.warn('Notif toggle failed', e);
    }
    updateNotifButton();
}

function updateNotifButton() {
    const btn = document.getElementById('side-menu-notif');
    if (!btn || !window.OneSignal?.Notifications) return;
    try {
        const permission = OneSignal.Notifications.permission;
        const optedIn    = OneSignal.User?.PushSubscription?.optedIn === true;
        const active     = permission && optedIn;
        btn.classList.remove('hidden');
        btn.setAttribute('aria-label', active ? 'Disattiva notifiche push' : 'Attiva notifiche push');
        const iconWrap = btn.querySelector('.side-menu-icon');
        if (iconWrap) {
            iconWrap.innerHTML = '<i data-feather="' + (active ? 'bell' : 'bell-off') + '" class="w-5 h-5"></i>';
        }
        const statusEl = document.getElementById('side-menu-notif-status');
        if (statusEl) {
            statusEl.textContent = active ? 'Attive' : 'Disattive';
            statusEl.className = 'block text-xs font-normal mt-0.5 ' + (active ? 'text-green-600' : 'text-gray-400');
        }
        if (window.feather) feather.replace();
    } catch (e) {
        console.warn('updateNotifButton failed', e);
    }
}

// ── Logout ────────────────────────────────────────────────────────────────────
async function handleLogout() {
    // Non chiamare OneSignal.logout(): la subscription resta legata all'external_id
    // così le push arrivano anche dopo Esci o scadenza sessione. Al login successivo
    // su questo device, OneSignal.login() aggiorna l'external_id al nuovo tecnico.
    await fetch('/auth/logout', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF } });
    window.location.href = '/';
}

// ── AGENDA (calendario + attività + ticket) ───────────────────────────────────
function initAgendaDate() {
    document.getElementById('agenda-date').value = todayIso();
    updateAgendaDateLabel();
}

function updateTodayButton() {
    const btn = document.getElementById('today-btn');
    if (!btn) return;
    const isToday = document.getElementById('agenda-date').value === todayIso();
    btn.classList.toggle('opacity-40', isToday);
}

function shiftDate(delta) {
    const input = document.getElementById('agenda-date');
    const d = new Date(input.value + 'T12:00:00');
    d.setDate(d.getDate() + delta);
    input.value = toLocalIsoDate(d);
    onDateChange();
}

function goToToday() {
    document.getElementById('agenda-date').value = todayIso();
    closeCalOverview();
    updateAgendaDateLabel();
    updateTodayButton();
    loaded.agenda = false;
    loadAgenda(true);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function onDateChange() {
    updateAgendaDateLabel();
    updateTodayButton();
    const date = document.getElementById('agenda-date').value;
    if (_agendaCache.calendar.length || _agendaCache.activities.length) {
        renderAgendaList();
    } else {
        loadAgenda();
    }
}

async function loadAgenda(force = false) {
    showState('agenda', 'loading');
    loaded.agenda = false;

    updateAgendaDateLabel();
    updateTodayButton();

    try {
        if (force || !_agendaCache.calendar.length) {
            const ok = await fetchAgendaData();
            if (!ok) return;
        }
        renderAgendaList();
        loaded.agenda = true;
    } catch (e) {
        showState('agenda', 'error');
    }
}

function renderSectionHeader(icon, title, count, colorClass) {
    if (!count) return '';
    return `<div class="flex items-center space-x-1.5 pt-2 pb-1 px-0.5">
        <i data-feather="${icon}" class="w-4 h-4 ${colorClass}"></i>
        <h3 class="text-sm font-semibold text-gray-600">${title}</h3>
        <span class="text-[10px] font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">${count}</span>
    </div>`;
}

function renderAgendaList() {
    const date = document.getElementById('agenda-date').value;
    const { orders, events, tickets } = getDailyItems(date);

    if (!orders.length && !events.length && !tickets.length) {
        showState('agenda', 'empty');
        return;
    }

    let html = '';
    if (orders.length) {
        html += renderSectionHeader('package', 'Ordini', orders.length, 'text-amber-500');
        html += orders.map(renderActivityCard).join('');
    }
    if (events.length) {
        html += renderSectionHeader('calendar', 'Eventi', events.length, 'text-sky-500');
        html += events.map(renderCalendarCard).join('');
    }
    if (tickets.length) {
        html += renderSectionHeader('message-square', 'Ticket', tickets.length, 'text-purple-400');
        html += tickets.map(renderTicketCard).join('');
    }

    document.getElementById('agenda-list').innerHTML = html;
    showState('agenda', 'list');
    feather.replace();
}

function renderTicketCard(t) {
    const status = t.status;
    const level  = t.ticket_level ?? t.level;
    const title  = t.title || 'Ticket';
    const timeLine = t.start_time
        ? `<div class="flex items-center text-xs text-gray-500 space-x-1 mb-1"><i data-feather="clock" class="w-3 h-3"></i><span>${t.start_time.slice(0,5)}${t.end_time ? ' → ' + t.end_time.slice(0,5) : ''}</span></div>`
        : '';

    return `
    <div class="bg-white rounded-2xl shadow-sm border border-purple-50 overflow-hidden" id="ticket-card-${t.id}">
        <div class="h-1 bg-purple-400"></div>
        <div class="p-4">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div class="flex flex-wrap gap-1.5 items-center">
                    ${level ? levelBadge(level) : ''}
                    ${statusBadge(status)}
                </div>
            </div>
            <span class="font-semibold text-gray-900 text-sm leading-tight">${esc(title)}</span>
            ${t.customer ? `<div class="flex items-center text-xs text-gray-500 space-x-1 mt-1 mb-1"><i data-feather="user" class="w-3 h-3"></i><span>${esc(t.customer)}</span></div>` : ''}
            ${timeLine}
            ${t.description ? `<p class="text-xs text-gray-400 mb-2">${esc(t.description)}</p>` : ''}
            <button onclick="openActivityDetail('calendar', ${t.id})"
                class="mt-2 w-full flex items-center justify-center space-x-1.5 text-xs text-purple-600 font-semibold py-2 border border-purple-200 rounded-xl active:bg-purple-50 transition-colors">
                <i data-feather="edit-2" class="w-3.5 h-3.5"></i>
                <span>Gestisci</span>
            </button>
        </div>
    </div>`;
}

function renderCalendarCard(ev) {
    const isSegnalazione = ev.event_type === 'segnalazione';
    const isMine = isSegnalazione && ev.technician_id === CURRENT_USER_ID;
    const color = ev.color || (isSegnalazione ? '#f97316' : '#0284c7');
    const sortedH = (ev.histories ?? []).slice().sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    const histories = sortedH.map(h =>
        `<li class="border-l-2 border-brand-200 pl-2 py-0.5 space-y-0.5">
            <div class="flex items-center justify-between gap-2">
                ${h.created_by ? `<span class="text-[10px] font-semibold text-brand-600">${h.created_by}</span>` : ''}
                <span class="text-[10px] text-gray-400 ml-auto">${formatDate(h.created_at)}</span>
            </div>
            <p class="text-xs text-gray-600 leading-snug">${h.note ?? ''}</p>
        </li>`
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
            ${ev.customer ? `<div class="flex items-center text-xs text-gray-500 space-x-1 mb-1"><i data-feather="user" class="w-3 h-3"></i><span>${ev.customer}</span></div>` : ''}
            ${ev.department ? `<div class="flex items-center text-xs text-gray-500 space-x-1 mb-1"><i data-feather="briefcase" class="w-3 h-3"></i><span>${ev.department}</span></div>` : ''}
            ${ev.description ? `<p class="text-xs text-gray-400 mb-2">${ev.description}</p>` : ''}
            ${histories ? `
            <details class="mt-1">
                <summary class="text-xs text-brand-600 cursor-pointer font-medium">Messaggi (${sortedH.length})</summary>
                <ul class="mt-2 space-y-2">${histories}</ul>
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
    return `
    <div class="bg-white rounded-2xl shadow-sm border border-amber-100 overflow-hidden">
        <div class="h-1 bg-amber-400"></div>
        <div class="px-4 pt-2 pb-0">
            <span class="text-[10px] font-bold uppercase tracking-wide text-amber-500">Ordine</span>
        </div>
        <div class="p-4 pt-2">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div>
                    <span class="font-semibold text-gray-900 text-sm">${esc(act.customer)}</span>
                    ${act.is_first ? '<span class="ml-2 text-[10px] font-bold px-2 py-0.5 rounded-full bg-accent-400/20 text-yellow-700">Prima installazione</span>' : ''}
                </div>
                ${statusBadge(act.status, act.status_label)}
            </div>
            ${renderActivityContactBlock(act)}
            ${renderActivityMapLinks(act)}
            <div class="flex items-center text-xs text-gray-500 space-x-1">
                <i data-feather="clock" class="w-3 h-3"></i>
                <span>${act.event_time ? act.event_time.slice(0, 5) : '—'}</span>
            </div>
            ${act.status !== 'completed' ? _buildGpsUpdateSection(act, 'card') : ''}
            <button onclick="openActivityDetail('activity', ${act.id})"
                class="mt-3 w-full flex items-center justify-center space-x-1.5 text-xs text-amber-600 font-semibold py-2 border border-amber-200 rounded-xl active:bg-amber-50 transition-colors">
                <i data-feather="edit-2" class="w-3.5 h-3.5"></i>
                <span>Gestisci</span>
            </button>
        </div>
    </div>`;
}

// ── CALENDAR MODAL (Mese / Anno) ─────────────────────────────────────────────

let _calOvYear  = null;
let _calOvMonth = null;
let _calOvView  = 'month';

const _CAL_MONTHS = ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
                     'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'];
const _CAL_MONTHS_SHORT = ['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];

async function openCalOverview() {
    const today = new Date();
    _calOvYear  = today.getFullYear();
    _calOvMonth = today.getMonth();
    _calOvView  = 'month';

    document.getElementById('cal-overview').classList.remove('hidden');
    document.getElementById('cal-ov-loading').classList.remove('hidden');
    document.getElementById('cal-ov-content').classList.add('hidden');
    document.body.style.overflow = 'hidden';
    setCalView('month');
    feather.replace();

    try {
        if (!_agendaCache.calendar.length) {
            const ok = await fetchAgendaData();
            if (!ok) { closeCalOverview(); return; }
        }
        document.getElementById('cal-ov-loading').classList.add('hidden');
        document.getElementById('cal-ov-content').classList.remove('hidden');
        _renderCalView();
    } catch (e) {
        document.getElementById('cal-ov-loading').innerHTML =
            `<div class="text-center px-4">
                <i data-feather="alert-circle" class="w-10 h-10 text-red-400 mx-auto mb-2"></i>
                <p class="text-gray-500 text-sm mb-3">Impossibile caricare il calendario.</p>
                <button onclick="openCalOverview()" class="text-brand-600 text-sm font-medium">Riprova</button>
             </div>`;
        feather.replace();
    }
}

function closeCalOverview() {
    document.getElementById('cal-overview').classList.add('hidden');
    document.body.style.overflow = '';
}

function setCalView(view) {
    _calOvView = view;
    document.getElementById('cal-view-month').className =
        'flex-1 text-xs font-semibold py-2.5 border-b-2 ' +
        (view === 'month' ? 'text-brand-600 border-brand-600' : 'text-gray-400 border-transparent');
    document.getElementById('cal-view-year').className =
        'flex-1 text-xs font-semibold py-2.5 border-b-2 ' +
        (view === 'year' ? 'text-brand-600 border-brand-600' : 'text-gray-400 border-transparent');
    document.getElementById('cal-ov-month-panel').classList.toggle('hidden', view !== 'month');
    document.getElementById('cal-ov-year-panel').classList.toggle('hidden', view !== 'year');
    _renderCalView();
}

function _renderCalView() {
    if (_calOvView === 'month') _renderCalOvGrid();
    else _renderCalOvYear();
}

function navCalOv(delta) {
    _calOvMonth += delta;
    if (_calOvMonth > 11) { _calOvMonth = 0;  _calOvYear++; }
    if (_calOvMonth < 0)  { _calOvMonth = 11; _calOvYear--; }
    _renderCalOvGrid();
}

function navCalYear(delta) {
    _calOvYear += delta;
    _renderCalOvYear();
}

function openCalMonth(monthIndex) {
    _calOvMonth = monthIndex;
    setCalView('month');
}

function goToDay(dateStr) {
    document.getElementById('agenda-date').value = dateStr;
    closeCalOverview();
    onDateChange();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function openCalEvent(type, id, e) {
    if (e) { e.stopPropagation(); }
    closeCalOverview();
    openActivityDetail(type, id);
}

function _getDayPreviewItems(dateStr) {
    const { orders, events, tickets } = getDailyItems(dateStr);
    const items = [];
    orders.forEach(o => items.push({ type: 'activity', id: o.id, label: o.customer ?? 'Ordine', color: '#ea8600', time: o.event_time }));
    events.forEach(ev => items.push({ type: 'calendar', id: ev.id, label: ev.title ?? 'Evento', color: ev.color || '#039be5', time: ev.start_time }));
    tickets.forEach(t => items.push({ type: 'calendar', id: t.id, label: t.title ?? t.customer ?? 'Ticket', color: '#9c27b0', time: t.start_time }));
    return items.sort((a, b) => sortKeyTime(a.time).localeCompare(sortKeyTime(b.time)));
}

function _isoDate(y, m, d) {
    return y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
}

function _renderCalCellEvents(items, dateStr, maxShow = 4) {
    if (!items.length) return '';
    const shown = items.slice(0, maxShow);
    let html = shown.map(it => {
        const time = it.time ? it.time.slice(0, 5) : '';
        const title = esc(it.label);
        return `<div class="cal-event-line cal-event-line-btn" role="button" tabindex="0"
            onclick="openCalEvent('${it.type}', ${it.id}, event)"
            title="${esc((time ? time + ' ' : '') + it.label)}">
            <span class="cal-event-dot" style="background:${it.color}"></span>
            <span class="cal-event-text">${time ? `<span class="cal-event-time">${time}</span> ${title}` : title}</span>
        </div>`;
    }).join('');
    if (items.length > maxShow) {
        const more = items.length - maxShow;
        html += `<div class="cal-more-link" role="button" tabindex="0" onclick="event.stopPropagation(); goToDay('${dateStr}')">${more} in più</div>`;
    }
    return `<div class="cal-day-events">${html}</div>`;
}

function _renderCalDayCell(dateStr, dayNum, otherMonth = false) {
    const today = todayIso();
    const isToday = !otherMonth && dateStr === today;
    const isPast  = dateStr < today;
    const previews = _getDayPreviewItems(dateStr);

    let cls = 'cal-day-cell';
    if (otherMonth) cls += ' other-month';
    if (isPast) cls += ' is-past';

    const numCls = 'cal-day-num' + (isToday ? ' today' : '');

    return `<div class="${cls}" aria-label="${dayNum}">
        <div class="cal-day-head" role="button" tabindex="0" onclick="goToDay('${dateStr}')" aria-label="Apri giorno ${dayNum}">
            <span class="${numCls}">${dayNum}</span>
        </div>
        ${_renderCalCellEvents(previews, dateStr)}
    </div>`;
}

function _renderCalOvYear() {
    document.getElementById('cal-ov-year-label').textContent = _calOvYear;
    let html = '';
    for (let m = 0; m < 12; m++) {
        const daysInMonth = new Date(_calOvYear, m + 1, 0).getDate();
        let count = 0;
        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = _calOvYear + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
            count += countItemsOnDate(dateStr);
        }
        const isCurrent = m === new Date().getMonth() && _calOvYear === new Date().getFullYear();
        html += `<button onclick="openCalMonth(${m})"
            class="bg-white rounded-2xl border ${isCurrent ? 'border-brand-400 ring-1 ring-brand-200' : 'border-gray-100'} p-4 text-left active:opacity-70 shadow-sm">
            <span class="text-sm font-bold text-gray-800">${_CAL_MONTHS_SHORT[m]}</span>
            <p class="text-[10px] text-gray-400 mt-1">${count} intervent${count === 1 ? 'o' : 'i'}</p>
        </button>`;
    }
    document.getElementById('cal-ov-year-grid').innerHTML = html;
    feather.replace();
}

function _renderCalOvGrid() {
    document.getElementById('cal-ov-month-label').textContent =
        _CAL_MONTHS[_calOvMonth] + ' ' + _calOvYear;

    const daysInMonth = new Date(_calOvYear, _calOvMonth + 1, 0).getDate();
    const startOffset = (new Date(_calOvYear, _calOvMonth, 1).getDay() + 6) % 7; // lun=0
    const prevMonth = _calOvMonth === 0 ? 11 : _calOvMonth - 1;
    const prevYear  = _calOvMonth === 0 ? _calOvYear - 1 : _calOvYear;
    const daysInPrevMonth = new Date(_calOvYear, _calOvMonth, 0).getDate();

    let html = '';

    // Giorni mese precedente (riempimento griglia)
    for (let i = 0; i < startOffset; i++) {
        const dayNum = daysInPrevMonth - startOffset + i + 1;
        html += _renderCalDayCell(_isoDate(prevYear, prevMonth, dayNum), dayNum, true);
    }

    // Giorni mese corrente
    for (let d = 1; d <= daysInMonth; d++) {
        html += _renderCalDayCell(_isoDate(_calOvYear, _calOvMonth, d), d, false);
    }

    // Giorni mese successivo (completa l'ultima settimana)
    const nextMonth = _calOvMonth === 11 ? 0 : _calOvMonth + 1;
    const nextYear  = _calOvMonth === 11 ? _calOvYear + 1 : _calOvYear;
    const trailing = (7 - ((startOffset + daysInMonth) % 7)) % 7;
    for (let d = 1; d <= trailing; d++) {
        html += _renderCalDayCell(_isoDate(nextYear, nextMonth, d), d, true);
    }

    document.getElementById('cal-ov-grid').innerHTML = `<div class="cal-grid">${html}</div>`;
    feather.replace();
}

function _formatCalOvDate(dateStr) {
    const [y, m, d] = dateStr.split('-');
    return `${parseInt(d)} ${_CAL_MONTHS[parseInt(m) - 1]} ${y}`;
}

// ── FATTURE CARTACEE ──────────────────────────────────────────────────────────
function toggleDelivered() {
    showDelivered = !showDelivered;
    const btn = document.getElementById('toggle-delivered-btn');
    btn.className = `text-xs font-medium flex items-center space-x-1 ${showDelivered ? 'text-brand-600' : 'text-gray-400'}`;
    reloadInvoices();
}

let toastTimer = null;
function showDeliverToast(message) {
    const toast = document.getElementById('deliver-toast');
    const msgEl = document.getElementById('deliver-toast-msg');
    if (msgEl && message) msgEl.textContent = message;
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

// Rilevamento GPS — restituisce true se ottenuto, false altrimenti
function getLocationOrSkip() {
    return new Promise(resolve => {
        if (!navigator.geolocation) { resolve(false); return; }
        navigator.geolocation.getCurrentPosition(
            pos => {
                invoiceCoords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                document.getElementById('invoice-address-input').value =
                    `${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)}`;
                resolve(true);
            },
            () => resolve(false),
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

// Ogni apertura Consegna fatture: GPS fresco + riordino percorso
async function openInvoicesSection() {
    showState('invoices', 'loading');
    await getLocationOrSkip();
    await loadInvoices();
}

// Ricarica senza reset coordinate (cambio mese/anno o toggle consegnate)
function reloadInvoices() {
    loaded.invoices = false;
    loadInvoices();
}

function getInvoiceSearchQuery() {
    return (document.getElementById('invoice-customer-search')?.value || '').trim().toLowerCase();
}

function filterInvoicesByCustomer(invoices) {
    const q = getInvoiceSearchQuery();
    if (!q) return invoices;
    return invoices.filter(inv => (inv.customer || '').toLowerCase().includes(q));
}

function onInvoiceCustomerSearchInput() {
    if (!loaded.invoices || !allInvoices.length) return;
    renderInvoicesList(filterInvoicesByCustomer(allInvoices));
}

function renderInvoicesList(data) {
    if (!data.length) {
        showState('invoices', getInvoiceSearchQuery() && allInvoices.length ? 'search-empty' : 'empty');
        return;
    }

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
            return `waze://?ll=${lat},${lng}&navigate=yes`;
        })();

        const directionsUrl = (() => {
            if (!inv.coordinates) return null;
            const dest = encodeURIComponent(inv.coordinates);
            const origin = prevCoords ? encodeURIComponent(prevCoords) : null;
            const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            if (isIos) {
                return origin
                    ? `comgooglemaps://?saddr=${origin}&daddr=${dest}&directionsmode=driving`
                    : `comgooglemaps://?daddr=${dest}&directionsmode=driving`;
            }
            const isMobileOrTablet = navigator.maxTouchPoints > 0;
            if (isMobileOrTablet) {
                const navParams = origin
                    ? `maps?saddr=${origin}&daddr=${dest}&dirflg=d`
                    : `maps?daddr=${dest}&dirflg=d`;
                return `intent://maps.google.com/${navParams}` +
                       `#Intent;scheme=https;package=com.google.android.apps.maps;end`;
            }
            return origin
                ? `https://maps.google.com/maps?saddr=${origin}&daddr=${dest}&dirflg=d`
                : `https://maps.google.com/maps?daddr=${dest}&dirflg=d`;
        })();

        const deliveredInfo = isDelivered
            ? `<div class="mt-3 space-y-2">
                   <div class="flex items-start space-x-1.5 text-xs text-green-600">
                       <i data-feather="check-circle" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5"></i>
                       <span>${formatDeliveredLabel(inv)}</span>
                   </div>
                   ${invoiceNeedsLatePayment(inv) ? `
                   <button type="button"
                       data-invoice-id="${inv.id}"
                       onclick="openInvoiceLatePaymentModalById(${inv.id}, this)"
                       class="w-full flex items-center justify-center gap-1.5 bg-amber-500 active:bg-amber-600 text-white text-xs font-semibold py-2.5 rounded-xl transition-colors">
                       <i data-feather="dollar-sign" class="w-3.5 h-3.5"></i>
                       <span>Segna pagato in contanti</span>
                   </button>` : ''}
                   ${showDelivered && invoiceCanUndoDelivery(inv) ? `
                   <button type="button"
                       data-invoice-id="${inv.id}"
                       onclick="undoInvoiceDelivery(this)"
                       class="w-full flex items-center justify-center gap-1.5 bg-white border border-red-200 text-red-700 active:bg-red-50 text-xs font-semibold py-2.5 rounded-xl transition-colors">
                       <i data-feather="rotate-ccw" class="w-3.5 h-3.5"></i>
                       <span>Annulla consegna</span>
                   </button>` : ''}
               </div>`
            : `<div class="mt-3">
                   <button
                       id="deliver-btn-${inv.id}"
                       data-invoice-id="${inv.id}"
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
                <p class="text-base font-bold text-gray-900 leading-snug mb-2">${esc(inv.customer)}</p>
                ${renderInvoiceContactBlock(inv)}
                ${renderInvoicePaymentBanner(inv)}
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
                ${deliveredInfo}
            </div>
        </div>`;
    }).join('');

    showState('invoices', 'list');
    feather.replace();
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

        if (!data.length) {
            allInvoices = [];
            showState('invoices', 'empty');
            loaded.invoices = true;
            return;
        }

        allInvoices = data;
        renderInvoicesList(filterInvoicesByCustomer(allInvoices));
        loaded.invoices = true;

    } catch (e) {
        showState('invoices', 'error');
    }
}

// Google Places Autocomplete (callback caricato dallo script Maps)
function setupPlacesAutocomplete(inputId, onSelect) {
    const input = document.getElementById(inputId);
    if (!input || !window.google) return;
    const autocomplete = new google.maps.places.Autocomplete(input, {
        types: ['geocode'],
        componentRestrictions: { country: 'it' },
        fields: ['geometry', 'formatted_address'],
    });
    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.geometry?.location) return;
        onSelect({
            lat: place.geometry.location.lat(),
            lng: place.geometry.location.lng(),
            label: place.formatted_address || input.value,
        });
    });
}

function onGoogleMapsLoaded() {
    setupPlacesAutocomplete('invoice-address-input', coords => {
        invoiceCoords = { lat: coords.lat, lng: coords.lng };
        loaded.invoices = false;
        loadInvoices();
    });
    setupPlacesAutocomplete('recovery-address-input', coords => {
        recoveryCoords = { lat: coords.lat, lng: coords.lng };
        loaded.recoveries = false;
        loadRecoveries();
    });
}

// ── Segna fattura come consegnata ─────────────────────────────────────────────
let _pendingInvoiceDeliver = null;
let _invoicePaymentAnswer = null;

function openInvoicePaymentModal(inv, btn) {
    _invoicePaymentAnswer = null;
    _pendingInvoiceDeliver = { inv, btn };
    const code = inv.invoice_code || inv.invoice_name || 'Fattura';
    document.getElementById('invoice-payment-summary').textContent =
        `${code} · ${getInvoiceBalanceDueLabel(inv)}`;
    document.getElementById('invoice-payment-note').value = '';
    document.getElementById('invoice-payment-cash').checked = false;
    document.getElementById('invoice-payment-cash-wrap').classList.add('hidden');
    document.getElementById('invoice-payment-note-wrap').classList.add('hidden');
    document.getElementById('invoice-payment-err').classList.add('hidden');
    ['invoice-payment-yes', 'invoice-payment-no'].forEach(id => {
        const el = document.getElementById(id);
        el.className = 'py-3 rounded-xl border-2 border-gray-200 text-sm font-semibold text-gray-700 active:bg-gray-50';
    });
    document.getElementById('invoice-payment-modal').classList.remove('hidden');
    feather.replace();
}

function closeInvoicePaymentModal() {
    document.getElementById('invoice-payment-modal').classList.add('hidden');
    _pendingInvoiceDeliver = null;
    _invoicePaymentAnswer = null;
}

function setInvoicePaymentAnswer(paid) {
    _invoicePaymentAnswer = paid;
    const yes = document.getElementById('invoice-payment-yes');
    const no  = document.getElementById('invoice-payment-no');
    yes.className = paid
        ? 'py-3 rounded-xl border-2 border-green-600 bg-green-50 text-sm font-semibold text-green-800'
        : 'py-3 rounded-xl border-2 border-gray-200 text-sm font-semibold text-gray-700 active:bg-gray-50';
    no.className = paid === false
        ? 'py-3 rounded-xl border-2 border-red-500 bg-red-50 text-sm font-semibold text-red-800'
        : 'py-3 rounded-xl border-2 border-gray-200 text-sm font-semibold text-gray-700 active:bg-gray-50';

    const cashWrap = document.getElementById('invoice-payment-cash-wrap');
    const noteWrap = document.getElementById('invoice-payment-note-wrap');
    const cashBox = document.getElementById('invoice-payment-cash');
    if (paid === true) {
        cashWrap.classList.remove('hidden');
        noteWrap.classList.remove('hidden');
        cashBox.checked = true;
    } else {
        cashWrap.classList.add('hidden');
        noteWrap.classList.add('hidden');
        cashBox.checked = false;
    }
}

function syncInvoicePaymentCashToggle() {
    const cashBox = document.getElementById('invoice-payment-cash');
    document.getElementById('invoice-payment-note-wrap').classList.toggle('hidden', !cashBox.checked);
}

async function confirmInvoiceDeliverWithPayment() {
    const pending = _pendingInvoiceDeliver;
    if (!pending) return;

    if (_invoicePaymentAnswer === null) {
        const err = document.getElementById('invoice-payment-err');
        err.textContent = 'Indica se la fattura è stata pagata (Sì o No).';
        err.classList.remove('hidden');
        return;
    }

    const paidInCash = _invoicePaymentAnswer === true
        && document.getElementById('invoice-payment-cash').checked;
    const paymentNote = document.getElementById('invoice-payment-note').value.trim();
    const cashBlock = {
        paidInCash,
        paymentNote: paidInCash && paymentNote ? paymentNote : null,
    };
    closeInvoicePaymentModal();
    await doDeliverInvoice(pending.inv, pending.btn, cashBlock);
}

let _pendingInvoiceLatePayment = null;

function openInvoiceLatePaymentModalById(invoiceId, btn) {
    const inv = allInvoices.find(i => i.id == invoiceId);
    if (!inv || !invoiceNeedsLatePayment(inv)) return;
    _pendingInvoiceLatePayment = { inv, btn };
    const code = inv.invoice_code || inv.invoice_name || 'Fattura';
    document.getElementById('invoice-late-payment-summary').textContent =
        `${code} · ${getInvoiceBalanceDueLabel(inv)}`;
    document.getElementById('invoice-late-payment-note').value = '';
    document.getElementById('invoice-late-payment-err').classList.add('hidden');
    document.getElementById('invoice-late-payment-modal').classList.remove('hidden');
    feather.replace();
}

function closeInvoiceLatePaymentModal() {
    document.getElementById('invoice-late-payment-modal').classList.add('hidden');
    _pendingInvoiceLatePayment = null;
}

async function confirmInvoiceLatePayment() {
    const pending = _pendingInvoiceLatePayment;
    if (!pending) return;
    const paymentNote = document.getElementById('invoice-late-payment-note').value.trim();
    closeInvoiceLatePaymentModal();
    await doDeliverInvoice(pending.inv, pending.btn, {
        paidInCash: true,
        paymentNote: paymentNote || null,
        latePayment: true,
    });
}

async function undoInvoiceDelivery(btn) {
    const id = parseInt(btn.dataset.invoiceId, 10);
    const inv = allInvoices.find(i => i.id == id);
    if (!inv || !invoiceCanUndoDelivery(inv)) return;

    const customer = inv.customer || 'questo cliente';
    const code = inv.invoice_code || inv.invoice_name || '';
    const label = code ? `${customer} (${code})` : customer;
    if (!confirm(`Annullare la consegna per ${label}?\n\nLa fattura tornerà tra quelle da consegnare.`)) return;

    const btnLabel = btn.querySelector('span')?.textContent || 'Annulla consegna';
    btn.disabled = true;
    if (btn.querySelector('span')) btn.querySelector('span').textContent = 'Invio…';

    try {
        const res = await fetch(`/api/technician/invoices/paper/${id}/undeliver`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });

        if (res.status === 401) { showSessionExpired(); return; }

        if (!res.ok) {
            let msg = res.status === 422 ? 'Operazione non consentita.' : 'Errore, riprova';
            try {
                const errJson = await res.json();
                if (errJson.message) msg = errJson.message;
            } catch (_) {}
            alert(msg);
            return;
        }

        showDeliverToast('Consegna annullata');
        loaded.invoices = false;
        await loadInvoices();
    } catch (e) {
        alert('Errore di rete, riprova.');
    } finally {
        btn.disabled = false;
        if (btn.querySelector('span')) btn.querySelector('span').textContent = btnLabel;
    }
}

async function markDelivered(btn) {
    const id = parseInt(btn.dataset.invoiceId, 10);
    const inv = allInvoices.find(i => i.id == id);
    if (!inv) return;

    if (invoiceNeedsPaymentModal(inv)) {
        openInvoicePaymentModal(inv, btn);
        return;
    }

    btn.disabled = true;
    btn.querySelector('span').textContent = 'Invio…';
    await doDeliverInvoice(inv, btn, null);
}

async function doDeliverInvoice(inv, btn, cashBlock) {
    const label = btn?.querySelector('span')?.textContent || 'Segna come consegnata';
    if (btn) { btn.disabled = true; if (btn.querySelector('span')) btn.querySelector('span').textContent = 'Invio…'; }

    const body = {};
    if (cashBlock) {
        if (cashBlock.paidInCash === true) {
            body.paid_in_cash = true;
            if (cashBlock.paymentNote) body.payment_note = cashBlock.paymentNote;
        } else if (cashBlock.paidInCash === false) {
            body.paid_in_cash = false;
        }
    }

    try {
        const res = await fetch(`/api/technician/invoices/paper/${inv.id}/deliver`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body),
        });

        if (res.status === 401) { showSessionExpired(); return; }

        if (!res.ok) {
            let msg = res.status === 422 ? 'Operazione non consentita.' : 'Errore, riprova';
            try {
                const errJson = await res.json();
                if (errJson.message) msg = errJson.message;
            } catch (_) {}
            alert(msg);
            if (btn) {
                btn.querySelector('span').textContent = label;
                btn.disabled = false;
            }
            return;
        }

        showDeliverToast(
            cashBlock?.latePayment
                ? 'Incasso contanti registrato'
                : (cashBlock?.paidInCash ? 'Consegna e incasso registrati' : 'Fattura segnata come consegnata')
        );
        if (cashBlock?.paidInCash === true) await loadTechnicianCashSummary();
        await new Promise(r => setTimeout(r, 900));

        await getLocationOrSkip();
        if (!invoiceCoords && inv.coordinates) {
            const [lat, lng] = inv.coordinates.split(',').map(Number);
            invoiceCoords = { lat, lng };
            document.getElementById('invoice-address-input').value =
                inv.display_address || inv.full_address || inv.coordinates;
        } else if (invoiceCoords) {
            document.getElementById('invoice-address-input').value =
                `${invoiceCoords.lat.toFixed(5)}, ${invoiceCoords.lng.toFixed(5)}`;
        }

        loaded.invoices = false;
        await loadInvoices();

    } catch (e) {
        if (btn) {
            btn.querySelector('span').textContent = label;
            btn.disabled = false;
        }
    }
}


// ── RECUPERO IMPIANTI ─────────────────────────────────────────────────────────
function toggleCompletedRecoveries() {
    showCompletedRecoveries = !showCompletedRecoveries;
    const btn = document.getElementById('toggle-completed-rec-btn');
    btn.className = `text-xs font-medium flex items-center space-x-1 ${showCompletedRecoveries ? 'text-brand-600' : 'text-gray-400'}`;
    reloadRecoveries();
}

let recoveryCoords = null;
let allRecoveries  = [];
let showCompletedRecoveries = false;
let _recoverySheetId = null;
let _recoverySheetData = null;
let _pendingRecoveryComplete = null;
let _recoveryPaymentAnswer = null;
let _recoveryPaymentDefaultInvoiceId = null;

function openRecoveryPaymentModal(item) {
    _recoveryPaymentAnswer = null;
    _recoveryPaymentDefaultInvoiceId = populateRecoveryPaymentInvoices(item);
    document.getElementById('recovery-payment-info').value = '';
    document.getElementById('recovery-payment-err').classList.add('hidden');
    document.getElementById('recovery-payment-unpaid-summary').textContent = getRecoveryUnpaidSummary(item);
    ['recovery-payment-yes', 'recovery-payment-no'].forEach(id => {
        const el = document.getElementById(id);
        el.className = 'py-3 rounded-xl border-2 border-gray-200 text-sm font-semibold text-gray-700 active:bg-gray-50';
    });
    document.getElementById('recovery-payment-modal').classList.remove('hidden');
    feather.replace();
}

function closeRecoveryPaymentModal() {
    document.getElementById('recovery-payment-modal').classList.add('hidden');
    _pendingRecoveryComplete = null;
    _recoveryPaymentAnswer = null;
    _recoveryPaymentDefaultInvoiceId = null;
}

function setRecoveryPaymentAnswer(paid) {
    _recoveryPaymentAnswer = paid;
    const yes = document.getElementById('recovery-payment-yes');
    const no  = document.getElementById('recovery-payment-no');
    yes.className = paid
        ? 'py-3 rounded-xl border-2 border-green-600 bg-green-50 text-sm font-semibold text-green-800'
        : 'py-3 rounded-xl border-2 border-gray-200 text-sm font-semibold text-gray-700 active:bg-gray-50';
    no.className = paid === false
        ? 'py-3 rounded-xl border-2 border-red-500 bg-red-50 text-sm font-semibold text-red-800'
        : 'py-3 rounded-xl border-2 border-gray-200 text-sm font-semibold text-gray-700 active:bg-gray-50';
}

function buildRecoveryCompleteNote(recoveryId, paymentBlock) {
    const extra = getRecoveryActionNote(recoveryId, 'complete');
    if (!paymentBlock) return extra;

    const paidLabel = paymentBlock.paid ? 'Sì' : 'No';
    let note = `LA FATTURA È STATA PAGATA? ${paidLabel}\nInfo tecnico: ${paymentBlock.info}`;
    if (extra) note += `\n\nNota recupero: ${extra}`;
    return note;
}

async function confirmRecoveryCompleteWithPayment() {
    const pending = _pendingRecoveryComplete;
    if (!pending) return;

    if (_recoveryPaymentAnswer === null) {
        const err = document.getElementById('recovery-payment-err');
        err.textContent = 'Indica se la fattura è stata pagata (Sì o No).';
        err.classList.remove('hidden');
        return;
    }

    const info = document.getElementById('recovery-payment-info').value.trim();
    if (!info) {
        const err = document.getElementById('recovery-payment-err');
        err.textContent = 'Scrivi un\'info per il backoffice.';
        err.classList.remove('hidden');
        return;
    }

    let invoiceId = null;
    if (_recoveryPaymentAnswer === true) {
        invoiceId = getSelectedRecoveryInvoiceId() ?? _recoveryPaymentDefaultInvoiceId;
        if (!invoiceId) {
            const err = document.getElementById('recovery-payment-err');
            err.textContent = 'Impossibile identificare la fattura da saldare.';
            err.classList.remove('hidden');
            return;
        }
    }

    const paymentBlock = { paid: _recoveryPaymentAnswer, info, invoiceId };
    closeRecoveryPaymentModal();
    await doRecoveryComplete(pending.recoveryId, pending.btn, paymentBlock);
}

async function doRecoveryComplete(recoveryId, btn, paymentBlock) {
    const note = buildRecoveryCompleteNote(recoveryId, paymentBlock);
    const body = {};
    if (note) body.note = note;

    if (paymentBlock) {
        if (paymentBlock.paid === true) {
            body.invoice_paid = true;
            body.invoice_id = paymentBlock.invoiceId;
            body.payment_note = paymentBlock.info;
        } else if (paymentBlock.paid === false) {
            body.invoice_paid = false;
        }
    }

    const label = btn?.querySelector('span')?.textContent || 'Recupero completato';
    if (btn) { btn.disabled = true; if (btn.querySelector('span')) btn.querySelector('span').textContent = 'Invio…'; }
    try {
        const res = await fetch(`/api/technician/equipment-recoveries/${recoveryId}/complete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body),
        });
        if (res.status === 401) { showSessionExpired(); return; }
        if (!res.ok) { alert('Errore durante l\'operazione.'); return; }
        clearRecoveryActionNote(recoveryId, 'complete');
        if (paymentBlock?.paid === true) await loadTechnicianCashSummary();
        await refreshRecoveryAfterAction('Recupero completato', true);
    } finally {
        if (btn) { btn.disabled = false; if (btn.querySelector('span')) btn.querySelector('span').textContent = label; }
    }
}

function getRecoveryLocationOrSkip() {
    return new Promise(resolve => {
        if (!navigator.geolocation) { resolve(false); return; }
        navigator.geolocation.getCurrentPosition(
            pos => {
                recoveryCoords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                const input = document.getElementById('recovery-address-input');
                if (input) input.value = `${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)}`;
                resolve(true);
            },
            () => resolve(false),
            { enableHighAccuracy: true, timeout: 8000 }
        );
    });
}

async function detectRecoveryLocation() {
    const btn   = document.getElementById('recovery-gps-btn');
    const input = document.getElementById('recovery-address-input');
    btn.disabled = true;
    input.value  = '';
    input.placeholder = 'Rilevamento…';
    recoveryCoords = null;
    await getRecoveryLocationOrSkip();
    btn.disabled = false;
    input.placeholder = 'Indirizzo o posizione…';
    if (recoveryCoords) {
        loaded.recoveries = false;
        await loadRecoveries();
    }
}

async function openRecoveriesSection() {
    showState('recoveries', 'loading');
    await getRecoveryLocationOrSkip();
    await loadRecoveries();
}

function reloadRecoveries() {
    loaded.recoveries = false;
    loadRecoveries();
}

async function loadRecoveries() {
    showState('recoveries', 'loading');
    loaded.recoveries = false;

    let url = '/api/technician/equipment-recoveries';
    const params = new URLSearchParams();
    if (recoveryCoords) {
        params.set('lat', recoveryCoords.lat);
        params.set('lng', recoveryCoords.lng);
    }
    if (showCompletedRecoveries) params.set('include_completed', '1');
    const qs = params.toString();
    if (qs) url += `?${qs}`;

    try {
        const res = await fetch(url, { headers: { 'X-CSRF-TOKEN': CSRF } });
        if (res.status === 401) { showSessionExpired(); return; }

        const json = await res.json();
        const data = json.data ?? [];

        if (!data.length) {
            showState('recoveries', 'empty');
            loaded.recoveries = true;
            return;
        }

        allRecoveries = data;
        const list = document.getElementById('recoveries-list');
        list.innerHTML = data.map((item, i) => {
            const done = isRecoveryCompleted(item);
            const distBadge = item.distance_km != null
                ? `<span class="flex items-center space-x-0.5 text-[10px] text-brand-600 font-semibold">
                       <i data-feather="navigation" class="w-3 h-3"></i>
                       <span>${item.distance_km < 1 ? (item.distance_km * 1000).toFixed(0) + ' m' : item.distance_km.toFixed(1) + ' km'}</span>
                   </span>`
                : '';
            const prevCoords = i === 0
                ? (recoveryCoords ? `${recoveryCoords.lat},${recoveryCoords.lng}` : null)
                : (data[i - 1].coordinates || null);
            const motivation = item.motivation || item.archive_notes || '';
            const mapAddress = getRecoveryMapAddress(item);

            return `
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border ${done ? 'border-green-100' : 'border-gray-100'}">
                ${recoveryCoords && !done ? `<div class="bg-gray-50 border-b border-gray-100 px-4 py-1 flex items-center justify-between">
                    <span class="text-[10px] text-gray-400 font-medium">#${i + 1} nel percorso</span>
                    ${distBadge}
                </div>` : ''}
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <p class="text-base font-bold text-gray-900 leading-snug">${esc(item.customer)}</p>
                        ${recoveryStatusBadge(item)}
                    </div>
                    ${renderRecoveryContactBlock(item)}
                    ${renderRecoveryInvoiceStatusBanner(item)}
                    ${motivation ? `<p class="text-xs text-gray-500 mb-2 leading-snug"><span class="font-semibold">Motivo:</span> ${esc(motivation)}</p>` : ''}
                    ${renderMapLinksHtml(item.coordinates, prevCoords, mapAddress)}
                    ${renderRecoveryContactsPreview(item.contacts)}
                    ${done
                        ? `<div class="mt-3 pt-3 border-t border-gray-100 text-xs text-green-600 flex items-center gap-1.5">
                               <i data-feather="check-circle" class="w-3.5 h-3.5"></i>
                               <span>${esc(item.status_label || 'Completato')}${item.archived_at_label ? ` · ${esc(item.archived_at_label)}` : ''}</span>
                           </div>`
                        : renderRecoveryCardActions(item)}
                </div>
            </div>`;
        }).join('');

        showState('recoveries', 'list');
        loaded.recoveries = true;
        feather.replace();
    } catch (e) {
        showState('recoveries', 'error');
    }
}

function openRecoverySheet() {
    document.getElementById('recovery-sheet').classList.remove('hidden');
    requestAnimationFrame(() => {
        document.getElementById('recovery-sheet-panel').style.transform = 'translateY(0)';
    });
}

function closeRecoverySheet() {
    const panel = document.getElementById('recovery-sheet-panel');
    panel.style.transform = 'translateY(100%)';
    setTimeout(() => {
        document.getElementById('recovery-sheet').classList.add('hidden');
        _recoverySheetId = null;
        _recoverySheetData = null;
    }, 350);
}

async function openRecoveryDetail(id) {
    _recoverySheetId = id;
    _recoverySheetData = null;
    openRecoverySheet();
    document.getElementById('recovery-sheet-loading').classList.remove('hidden');
    document.getElementById('recovery-sheet-content').classList.add('hidden');
    document.getElementById('recovery-sheet-badge').innerHTML = '';

    try {
        const res = await fetch(`/api/technician/equipment-recoveries/${id}`, { headers: { 'X-CSRF-TOKEN': CSRF } });
        if (res.status === 401) { showSessionExpired(); return; }
        if (!res.ok) throw new Error('fetch failed');
        const json = await res.json();
        _recoverySheetData = json.data ?? json;
        renderRecoverySheet(_recoverySheetData);
    } catch (e) {
        document.getElementById('recovery-sheet-loading').classList.add('hidden');
        document.getElementById('recovery-sheet-content').classList.remove('hidden');
        document.getElementById('recovery-sheet-content').innerHTML =
            `<p class="text-sm text-red-500">Impossibile caricare il dettaglio.</p>`;
    }
}

function renderRecoverySheet(item) {
    const done = isRecoveryCompleted(item);
    document.getElementById('recovery-sheet-badge').innerHTML =
        `${recoveryStatusBadge(item)}${item.archived_at_label ? `<span class="text-xs text-gray-400 truncate">${esc(item.archived_at_label)}</span>` : ''}`;

    const motivation = item.motivation || '';
    const archiveNotes = item.archive_notes || '';
    const prevCoords = recoveryCoords ? `${recoveryCoords.lat},${recoveryCoords.lng}` : null;

    let actionsHtml = '';
    if (!done) {
        actionsHtml = `
        <div class="space-y-4 pt-2 border-t border-gray-100">
            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wide">Registra contatto</label>
                <textarea id="recovery-contact-note" rows="3" placeholder="Nota obbligatoria…"
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 resize-none focus:outline-none focus:border-brand-400 bg-gray-50"></textarea>
                <button type="button" onclick="submitRecoveryContact(${item.id}, this)"
                    class="w-full bg-brand-600 active:bg-brand-700 text-white text-xs font-semibold py-2.5 rounded-xl">
                    Registra contatto
                </button>
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wide">Recupero completato</label>
                <textarea id="recovery-complete-note" rows="2" placeholder="Nota opzionale…"
                    class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 resize-none focus:outline-none focus:border-brand-400 bg-gray-50"></textarea>
                <button type="button" onclick="submitRecoveryComplete(${item.id}, this)"
                    class="w-full bg-green-600 active:bg-green-700 text-white text-xs font-semibold py-2.5 rounded-xl">
                    Segna recupero completato
                </button>
            </div>
        </div>`;
    }

    document.getElementById('recovery-sheet-content').innerHTML = `
        <div>
            <h3 class="text-lg font-bold text-gray-900 leading-snug mb-3">${esc(item.customer || '—')}</h3>
            ${renderRecoveryInvoiceStatusBanner(item)}
            ${renderRecoveryContactBlock(item)}
            ${renderMapLinksHtml(item.coordinates, prevCoords, getRecoveryMapAddress(item))}
        </div>
        ${motivation ? `<div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Motivazione archiviazione</p>
            <p class="text-sm text-gray-700 leading-snug">${esc(motivation)}</p>
        </div>` : ''}
        ${archiveNotes && archiveNotes !== motivation ? `<div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Note archiviazione</p>
            <p class="text-sm text-gray-600 leading-snug whitespace-pre-wrap">${esc(archiveNotes)}</p>
        </div>` : ''}
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Storico contatti backoffice</p>
            ${renderRecoveryContactsTimeline(item.contacts)}
        </div>
        ${actionsHtml}
    `;

    document.getElementById('recovery-sheet-loading').classList.add('hidden');
    document.getElementById('recovery-sheet-content').classList.remove('hidden');
    feather.replace();
}

async function refreshRecoveryAfterAction(message, closeSheet = false) {
    showDeliverToast(message);
    if (closeSheet) closeRecoverySheet();
    await getRecoveryLocationOrSkip();
    loaded.recoveries = false;
    await loadRecoveries();
}

function getRecoveryActionNote(recoveryId, type) {
    const listEl = document.getElementById(`recovery-${type}-note-${recoveryId}`);
    if (listEl) return listEl.value.trim();
    if (_recoverySheetId === recoveryId) {
        return document.getElementById(`recovery-${type}-note`)?.value.trim() || '';
    }
    return '';
}

function clearRecoveryActionNote(recoveryId, type) {
    const listEl = document.getElementById(`recovery-${type}-note-${recoveryId}`);
    if (listEl) listEl.value = '';
    if (_recoverySheetId === recoveryId) {
        const sheetEl = document.getElementById(`recovery-${type}-note`);
        if (sheetEl) sheetEl.value = '';
    }
}

async function submitRecoveryContact(recoveryId, btn) {
    const note = getRecoveryActionNote(recoveryId, 'contact');
    if (!note) {
        alert('Inserisci una nota per registrare il contatto.');
        return;
    }
    const label = btn?.querySelector('span')?.textContent || 'Registra contatto';
    if (btn) { btn.disabled = true; if (btn.querySelector('span')) btn.querySelector('span').textContent = 'Invio…'; }
    try {
        const res = await fetch(`/api/technician/equipment-recoveries/${recoveryId}/contact`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ note }),
        });
        if (res.status === 401) { showSessionExpired(); return; }
        if (!res.ok) { alert('Errore durante la registrazione.'); return; }
        clearRecoveryActionNote(recoveryId, 'contact');
        if (_recoverySheetId === recoveryId) await openRecoveryDetail(recoveryId);
        await refreshRecoveryAfterAction('Contatto registrato');
    } finally {
        if (btn) { btn.disabled = false; if (btn.querySelector('span')) btn.querySelector('span').textContent = label; }
    }
}

async function submitRecoveryComplete(recoveryId, btn) {
    const item = getRecoveryItem(recoveryId);
    if (recoveryHasUnpaidInvoices(item)) {
        _pendingRecoveryComplete = { recoveryId, btn };
        openRecoveryPaymentModal(item);
        return;
    }
    if (!confirm('Confermi che il recupero impianto è completato?')) return;
    await doRecoveryComplete(recoveryId, btn, null);
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
    activity: [
        { value: 'open',      label: 'Aperto' },
        { value: 'suspended', label: 'Sospeso' },
        { value: 'completed', label: 'Completato' },
    ],
    ticket: [
        { value: 'open',    label: 'Aperto' },
        { value: 'pending', label: 'In attesa' },
        { value: 'close',   label: 'Chiuso' },
    ],
};

function activityCanEditProducts(d) {
    const p = d?.permissions ?? {};
    const u = d?.ui ?? {};
    if (p.can_edit_products === false) return false;
    if (u.show_delete_product === false) return false;
    return true;
}

function activityCanChangeStatus(d) {
    const p = d?.permissions ?? {};
    const u = d?.ui ?? {};
    if (p.can_change_status === false) return false;
    if (u.show_status_select === false) return false;
    return true;
}

async function openActivityDetail(type, id) {
    _sheetType = type;
    _sheetId   = id;
    _sheetData = null;

    document.getElementById('sheet-loading').classList.remove('hidden');
    document.getElementById('sheet-loading').innerHTML = `
        <div class="skeleton h-5 rounded-lg w-3/4"></div>
        <div class="skeleton h-4 rounded-lg w-1/2"></div>
        <div class="skeleton h-24 rounded-xl w-full mt-2"></div>`;
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
        if (type === 'ticket') {
            _sheetData = _agendaCache.tickets.find(t => t.id === id) ?? null;
            if (!_sheetData) throw new Error('Ticket non trovato');
            _renderSheetContent();
            return;
        }

        const paths = {
            calendar: `/api/technician/calendar-events/${id}`,
            activity: `/api/technician/cart-activities/${id}`,
        };
        const res = await fetch(paths[type], { headers: { 'X-CSRF-TOKEN': CSRF } });
        if (res.status === 401) { showSessionExpired(); closeActivitySheet(); return; }

        let json = null;
        try { json = await res.json(); } catch (_) {}

        if (!res.ok) {
            const apiMsg = json?.message || json?.error;
            throw new Error(apiMsg || `Errore del server (${res.status})`);
        }

        _sheetData = json?.data ?? json;

        if (type === 'activity' && _availableProducts === null) {
            await _loadAvailableProducts();
        }

        _renderSheetContent();
    } catch (e) {
        console.warn('openActivityDetail failed', type, id, e);
        const errText = (e?.message && e.message !== 'fetch failed') ? e.message : 'Impossibile caricare i dettagli.';
        document.getElementById('sheet-loading').classList.remove('hidden');
        document.getElementById('sheet-loading').innerHTML = `
            <div class="text-center py-10 px-4">
                <i data-feather="alert-circle" class="w-10 h-10 text-red-400 mx-auto mb-2"></i>
                <p class="text-gray-700 text-sm font-medium mb-1">${esc(errText)}</p>
                <p class="text-gray-400 text-xs mb-4">Riprova tra poco. Se continua, chiudi e riapri l'ordine oppure aggiorna la pagina.</p>
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
        calendar: { label: 'Evento',    cls: 'text-brand-700 bg-brand-50',   icon: 'calendar' },
        activity: { label: 'Ordine',    cls: 'text-amber-700 bg-amber-50',   icon: 'package' },
        ticket:   { label: 'Ticket',    cls: 'text-purple-700 bg-purple-50', icon: 'message-square' },
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
    if (_sheetType === 'activity') el.innerHTML = _buildActivityContent(_sheetData);
    if (_sheetType === 'ticket')   el.innerHTML = _buildTicketContent(_sheetData);
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

function _buildFormSection(currentStatus, notes = [], formOpts = {}) {
    const { readOnlyStatus = false, statusLabel = null } = formOpts;
    const opts = (SHEET_STATUS_OPTIONS[_sheetType] ?? []).map(o =>
        `<option value="${o.value}"${currentStatus === o.value ? ' selected' : ''}>${o.label}</option>`
    ).join('');
    const statusField = readOnlyStatus
        ? `<div id="sheet-status-readonly">${statusBadge(currentStatus, statusLabel)}</div>`
        : `<select id="sheet-status" class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-brand-400">${opts}</select>`;
    const list = notes.map(n => `
        <div class="border-l-2 border-brand-200 pl-3">
            <p class="text-sm text-gray-700 leading-snug">${esc(n.body ?? n.note ?? '')}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">${n.created_by ? esc(n.created_by) + ' · ' : ''}${formatDate(n.created_at)}</p>
        </div>`).join('');
    return `<div class="bg-gray-50 rounded-2xl p-4 space-y-4">
        <div class="space-y-2">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Stato</p>
            ${statusField}
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
    const sortedHistories = (d.histories ?? [])
        .slice()
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    const historiesHtml = sortedHistories.map(h => `
        <div class="border-l-2 border-brand-200 pl-3 space-y-0.5">
            <div class="flex items-center justify-between gap-2">
                ${h.created_by ? `<span class="text-[10px] font-semibold text-brand-600">${esc(h.created_by)}</span>` : ''}
                <span class="text-[10px] text-gray-400 ml-auto">${formatDate(h.created_at)}</span>
            </div>
            <p class="text-xs text-gray-600 leading-snug">${esc(h.note ?? '')}</p>
        </div>`).join('');
    return `
    <div class="space-y-1">
        <h2 class="text-lg font-bold text-gray-900 leading-snug">${esc(d.title ?? '')}</h2>
        ${_infoRow('user', d.customer)}
        ${_infoRow('clock', formatDate(d.start_date, d.start_time) + ' → ' + formatDate(d.end_date, d.end_time))}
        ${d.department ? _infoRow('briefcase', d.department) : ''}
        ${d.description ? `<p class="text-sm text-gray-500 pt-1">${esc(d.description)}</p>` : ''}
    </div>
    ${_buildFormSection(d.status, d.notes ?? [])}
    ${_buildAttachmentsSection(d.attachments ?? [])}
    ${historiesHtml ? `<div class="space-y-2">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Messaggi</p>
        <div class="space-y-3">${historiesHtml}</div>
    </div>` : ''}`;
}

// ── Cart Activity ─────────────────────────────────────────────────────────────
function _buildCollectFromCustomerSection(d) {
    const collect = d.collect_from_customer ?? d.customer_cash_due;
    if (!collect) return '';
    const total = parseFloat(collect.total ?? 0);
    if (!(total > 0)) return '';

    const lines = (collect.lines ?? []).map(line => `
        <div class="flex items-center justify-between gap-3 py-2 border-b border-green-100 last:border-0">
            <p class="text-sm text-gray-800 flex-1 min-w-0 leading-snug">${esc(line.label ?? '')}</p>
            <span class="text-sm font-semibold text-gray-900 flex-shrink-0">${formatEuro(line.amount)}</span>
        </div>`).join('');

    return `<div id="sheet-collect-wrap">
        <div class="bg-green-50 border-2 border-green-300 rounded-2xl p-4 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold text-green-800 uppercase tracking-wide">Da incassare dal cliente</p>
                    <p class="text-2xl font-bold text-green-900 mt-1">${formatEuro(total)}</p>
                </div>
                <i data-feather="dollar-sign" class="w-6 h-6 text-green-600 flex-shrink-0"></i>
            </div>
            ${lines ? `<div class="rounded-xl bg-white/70 px-3 border border-green-100">${lines}</div>` : ''}
            <p class="text-[11px] text-green-800/80 leading-snug">L'abbonamento verrà fatturato al cliente, non va incassato in contanti.</p>
        </div>
    </div>`;
}

function _buildActivityContent(d) {
    const offer = d.offer;
    const offerPrice = offer
        ? (offer.price_display ?? offer.public_price ?? offer.price_vat ?? offer.price_with_vat ?? offer.price ?? 0)
        : 0;
    const offerVatNote = offer && (offer.price_includes_vat === true || offer.public_price != null)
        ? `<p class="text-[11px] text-brand-600/80">IVA inclusa</p>`
        : '';
    const offerHtml = offer ? `
    <div class="bg-brand-50 border border-brand-100 rounded-2xl p-4 space-y-1">
        <p class="text-xs font-bold text-brand-600 uppercase tracking-wide">Piano contrattato</p>
        <p class="text-base font-bold text-gray-900">${esc(offer.name ?? '')}</p>
        ${offer.description ? `<p class="text-xs text-gray-500">${esc(offer.description)}</p>` : ''}
        <p class="text-sm font-semibold text-brand-700">${formatEuro(offerPrice)} / mese</p>
        ${offerVatNote}
    </div>` : '';

    return `
    <div class="space-y-1">
        <h2 class="text-lg font-bold text-gray-900">${esc(d.customer ?? '')}</h2>
        ${renderActivityContactBlock(d)}
        ${renderActivityMapLinks(d)}
        ${_infoRow('clock', (d.event_at ? formatDate(d.event_at) : '') + (d.event_time ? ' ' + d.event_time.slice(0,5) : ''))}
        ${d.is_first ? '<span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-accent-400/20 text-yellow-700 mt-1">Prima installazione</span>' : ''}
    </div>
    ${offerHtml}
    ${_buildCollectFromCustomerSection(d)}
    ${_buildExtraProductsSection(d)}
    ${_buildAttachmentsSection(d.attachments ?? [])}
    ${_buildGpsUpdateSection(d, 'sheet')}
    ${_buildFormSection(d.status, d.notes ?? [], {
        readOnlyStatus: !activityCanChangeStatus(d),
        statusLabel: d.status_label ?? null,
    })}`;
}

function _buildTicketContent(t) {
    return `
    <div class="space-y-1">
        <h2 class="text-lg font-bold text-gray-900">${esc(t.customer ?? 'Ticket')}</h2>
        <div class="flex flex-wrap gap-1.5 pt-1">
            ${t.ticket_level ? levelBadge(t.ticket_level) : ''}
            ${statusBadge(t.ticket_status)}
        </div>
        ${_infoRow('hash', 'Ticket #' + t.id)}
        ${t.messages_count ? _infoRow('message-square', t.messages_count + ' messaggi') : ''}
        ${t.updated_at ? _infoRow('clock', 'Aggiornato ' + formatDate(t.updated_at.slice(0, 10), t.updated_at.slice(11, 19))) : ''}
    </div>
    ${_buildFormSection(t.ticket_status, [])}
    ${_buildAttachmentsSection(t.attachments ?? [])}`;
}

function _buildExtraProductsSection(d) {
    const extras = d.extra_products ?? [];
    const total  = parseFloat(d.extra_products_total ?? 0);
    const canEdit = activityCanEditProducts(d);

    const rows = extras.map(ep => `
        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
            <div class="flex-1 min-w-0 mr-3">
                <p class="text-sm font-medium text-gray-800 truncate">${esc(ep.name)}</p>
                <p class="text-xs text-gray-400">€ ${parseFloat(ep.price).toFixed(2)} × ${ep.quantity}</p>
            </div>
            <div class="flex items-center space-x-2 flex-shrink-0">
                <span class="text-sm font-semibold text-gray-700">€ ${parseFloat(ep.subtotal).toFixed(2)}</span>
                ${canEdit ? `<button onclick="removeExtraProduct(${d.id},${ep.id})" class="p-1 text-red-400 active:text-red-600">
                    <i data-feather="trash-2" class="w-4 h-4"></i>
                </button>` : ''}
            </div>
        </div>`).join('');

    const prodOpts = (_availableProducts ?? []).map(p =>
        `<option value="${p.id}" data-price="${p.price}">[${p.type === 'supplement' ? 'Suppl.' : 'Prod.'}] ${esc(p.name)} — €${parseFloat(p.price).toFixed(2)}</option>`
    ).join('');

    const addForm = canEdit ? `
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
        <p id="sheet-extra-fb" class="hidden text-xs text-green-600 font-medium">Prodotto aggiunto.</p>` : '';

    return `<div class="space-y-3" id="sheet-extras-wrap">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Aggiunte installazione</p>
        <div class="divide-y divide-gray-100 rounded-xl border border-gray-100 px-3">
            ${rows || '<p class="text-xs text-gray-400 py-3">Nessun prodotto aggiunto.</p>'}
        </div>
        <div class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-xl">
            <span class="text-sm font-semibold text-gray-600">Totale extra</span>
            <span class="text-sm font-bold text-gray-900" id="sheet-extras-total">€ ${total.toFixed(2)}</span>
        </div>
        ${addForm}
    </div>`;
}

// ── Sheet actions ─────────────────────────────────────────────────────────────
async function saveSheetChanges() {
    const statusEl  = document.getElementById('sheet-status');
    const status    = statusEl ? statusEl.value : (_sheetData?.status ?? 'open');
    const canChangeStatus = _sheetType !== 'activity' || activityCanChangeStatus(_sheetData);
    const noteInput = document.getElementById('sheet-note-input');
    const note      = (noteInput?.value ?? '').trim();
    const btn       = document.getElementById('sheet-save-btn');
    const fb        = document.getElementById('sheet-save-fb');

    if (_sheetType === 'activity' && canChangeStatus && status === 'suspended' && !note) {
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
        if (_sheetType === 'ticket') {
            const res = await fetch(`/api/technician/tickets/${_sheetId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ ticket_status: status }),
            });
            if (res.status === 401) { showSessionExpired(); return; }
            if (!res.ok) throw new Error('save failed');
            const json = await res.json();
            if (json.data) _sheetData = { ..._sheetData, ...json.data };
            const idx = _agendaCache.tickets.findIndex(t => t.id === _sheetId);
            if (idx >= 0) _agendaCache.tickets[idx].ticket_status = _sheetData.ticket_status ?? status;
            if (note) {
                await fetch(`/api/technician/tickets/${_sheetId}/notes`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ body: note }),
                });
            }
            fb.classList.remove('hidden');
            await new Promise(r => setTimeout(r, 900));
            closeActivitySheet();
            renderAgendaList();
            return;
        }

        if (_sheetType === 'calendar' || _sheetType === 'activity') {
            const url    = _sheetType === 'calendar'
                ? `/api/technician/calendar-events/${_sheetId}`
                : `/api/technician/cart-activities/${_sheetId}`;
            const body   = {};
            if (canChangeStatus) body.status = status;
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
                if (_sheetType === 'calendar') {
                    const ev = _agendaCache.calendar.find(e => e.id === _sheetId);
                    if (ev) { ev.status = _sheetData.status ?? status; }
                }
                if (_sheetType === 'activity') {
                    const act = _agendaCache.activities.find(a => a.id === _sheetId);
                    if (act) { act.status = _sheetData.status ?? status; }
                    if (status === 'completed') await loadTechnicianCashSummary();
                    fb.classList.remove('hidden');
                    await new Promise(r => setTimeout(r, 1200));
                    closeActivitySheet();
                    renderAgendaList();
                    return;
                }
            }
        }

        _renderSheetContent();
        renderAgendaList();
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
        activity: `/api/technician/cart-activities/${_sheetId}/attachments`,
        ticket:   `/api/technician/tickets/${_sheetId}/attachments`,
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

function _buildGpsUpdateSection(d, prefix = 'sheet') {
    if (d.status === 'completed') return '';

    const id = d.id;
    const coords = d.coordinates ? esc(d.coordinates) : '';
    const btnId = `${prefix}-gps-btn-${id}`;
    const labelId = `${prefix}-gps-label-${id}`;
    const coordsId = `${prefix}-gps-coords-${id}`;
    const fbId = `${prefix}-gps-fb-${id}`;
    const errId = `${prefix}-gps-err-${id}`;

    return `<div class="bg-sky-50 border border-sky-100 rounded-2xl p-4 space-y-3" id="${prefix}-gps-wrap-${id}">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Posizione GPS impianto</p>
        <p class="text-xs text-gray-500">Usa questa funzione sul posto per rendere Maps/Waze più precisi. L'indirizzo civico non viene modificato.</p>
        <p class="text-xs text-gray-600" id="${coordsId}">${coords
            ? `<span class="font-medium">Attuali:</span> ${coords}`
            : '<span class="text-gray-400">Nessuna coordinata salvata.</span>'}</p>
        <button type="button" onclick="updatePlantGps(${id}, '${prefix}')" id="${btnId}"
            class="w-full flex items-center justify-center space-x-2 text-sm font-semibold bg-sky-600 text-white py-3 rounded-xl active:bg-sky-700 disabled:opacity-50">
            <i data-feather="crosshair" class="w-4 h-4"></i>
            <span id="${labelId}">Aggiorna posizione GPS</span>
        </button>
        <p id="${fbId}" class="hidden text-xs text-green-600 font-medium text-center"></p>
        <p id="${errId}" class="hidden text-xs text-red-500 font-medium text-center"></p>
    </div>`;
}

async function updatePlantGps(activityId, prefix = 'sheet') {
    const btn   = document.getElementById(`${prefix}-gps-btn-${activityId}`);
    const label = document.getElementById(`${prefix}-gps-label-${activityId}`);
    const fb    = document.getElementById(`${prefix}-gps-fb-${activityId}`);
    const err   = document.getElementById(`${prefix}-gps-err-${activityId}`);

    fb.classList.add('hidden');
    err.classList.add('hidden');

    if (!navigator.geolocation) {
        err.textContent = 'Geolocalizzazione non supportata da questo dispositivo.';
        err.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    if (label) label.textContent = 'Rilevamento…';

    navigator.geolocation.getCurrentPosition(async (pos) => {
        const coordinates = `${pos.coords.latitude},${pos.coords.longitude}`;

        try {
            const res = await fetch(`/api/technician/cart-activities/${activityId}/plant-coordinates`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ coordinates }),
            });

            if (res.status === 401) { showSessionExpired(); return; }

            const json = await res.json().catch(() => ({}));

            if (res.ok) {
                const coordsEl = document.getElementById(`${prefix}-gps-coords-${activityId}`);
                if (coordsEl) {
                    coordsEl.innerHTML = `<span class="font-medium">Attuali:</span> ${esc(json.data?.coordinates ?? coordinates)}`;
                }
                const cardCoordsEl = document.getElementById(`card-gps-coords-${activityId}`);
                if (cardCoordsEl) {
                    cardCoordsEl.innerHTML = `<span class="font-medium">Attuali:</span> ${esc(json.data?.coordinates ?? coordinates)}`;
                }
                if (_sheetData && json.data) {
                    Object.assign(_sheetData, json.data);
                }
                const act = _agendaCache.activities.find(a => a.id === activityId);
                if (act && json.data) {
                    if (json.data.coordinates) act.coordinates = json.data.coordinates;
                    if (json.data.maps_url) act.maps_url = json.data.maps_url;
                    if (json.data.waze_url) act.waze_url = json.data.waze_url;
                }
                fb.textContent = 'Posizione GPS aggiornata.';
                fb.classList.remove('hidden');
            } else {
                err.textContent = json.message ?? 'Errore durante l\'aggiornamento.';
                err.classList.remove('hidden');
            }
        } catch (_) {
            err.textContent = 'Errore di rete. Riprova.';
            err.classList.remove('hidden');
        }

        btn.disabled = false;
        if (label) label.textContent = 'Aggiorna posizione GPS';
        feather.replace();
    }, (geoErr) => {
        const messages = {
            1: 'Permesso posizione negato. Abilita il GPS nelle impostazioni del telefono.',
            2: 'Posizione non disponibile. Riprova all\'aperto.',
            3: 'Timeout GPS. Riprova.',
        };
        err.textContent = messages[geoErr.code] ?? 'Impossibile rilevare la posizione.';
        err.classList.remove('hidden');
        btn.disabled = false;
        if (label) label.textContent = 'Aggiorna posizione GPS';
    }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 });
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

    const extrasWrap = document.getElementById('sheet-extras-wrap');
    if (extrasWrap) extrasWrap.outerHTML = _buildExtraProductsSection(_sheetData);

    const collectHtml = _buildCollectFromCustomerSection(_sheetData);
    const collectWrap = document.getElementById('sheet-collect-wrap');
    if (collectWrap) {
        if (collectHtml) collectWrap.outerHTML = collectHtml;
        else collectWrap.remove();
    } else if (collectHtml) {
        const anchor = document.getElementById('sheet-extras-wrap');
        if (anchor) anchor.insertAdjacentHTML('beforebegin', collectHtml);
    }

    feather.replace();
}

// ── Init ──────────────────────────────────────────────────────────────────────
async function openDeepLinkedEvent(eventId) {
    const id = parseInt(eventId, 10);
    if (!id) return;
    try {
        const res = await fetch(`/api/technician/calendar-events/${id}`, {
            headers: { 'X-CSRF-TOKEN': CSRF },
        });
        if (res.status === 401) { showSessionExpired(); return; }
        if (!res.ok) return;
        const json = await res.json();
        const event = json.data ?? json;
        if (event.start_date) {
            document.getElementById('agenda-date').value = event.start_date.slice(0, 10);
            await loadAgenda();
        }
        await openActivityDetail('calendar', id);
        if (window.history?.replaceState) {
            const clean = new URL(window.location.href);
            clean.searchParams.delete('eventId');
            clean.searchParams.delete('event_id');
            window.history.replaceState({}, '', clean.pathname + clean.search);
        }
    } catch (e) {
        console.warn('Deep link evento fallito', e);
    }
}

async function handleDeepLinkFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const eventId = params.get('eventId') || params.get('event_id');
    if (eventId) await openDeepLinkedEvent(eventId);
}

function getEventIdFromPushContext(notification) {
    const params = new URLSearchParams(window.location.search);
    const fromPage = params.get('eventId') || params.get('event_id');
    if (fromPage) return fromPage;

    const rawUrl = notification?.launchURL
        || notification?.url
        || notification?.additionalData?.url
        || '';
    if (!rawUrl) return null;
    try {
        const parsed = new URL(rawUrl, window.location.origin);
        return parsed.searchParams.get('eventId') || parsed.searchParams.get('event_id');
    } catch (_) {
        return null;
    }
}

@if(config('services.onesignal.app_id'))
function initOneSignalPush() {
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    OneSignalDeferred.push(async function (OneSignal) {
        window.OneSignal = OneSignal;
        try {
            await OneSignal.init({
                appId: @json(config('services.onesignal.app_id')),
                safari_web_id: @json(config('services.onesignal.safari_web_id')),
                serviceWorkerPath: 'tech/OneSignalSDKWorker.js',
                serviceWorkerParam: { scope: '/tech/' },
                notifyButton: { enable: false },
                allowLocalhostAsSecureOrigin: @json(app()->environment('local')),
            });

            const techUserId = @json($userId ? (string) $userId : null);
            if (techUserId) {
                try {
                    await OneSignal.login(techUserId);
                } catch (e) {
                    console.warn('OneSignal.login failed', e);
                }
            }

            updateNotifButton();
            OneSignal.User.PushSubscription.addEventListener('change', updateNotifButton);
            OneSignal.Notifications.addEventListener('permissionChange', updateNotifButton);
            OneSignal.Notifications.addEventListener('click', (event) => {
                const eventId = getEventIdFromPushContext(event.notification);
                if (eventId) {
                    openDeepLinkedEvent(eventId);
                } else {
                    handleDeepLinkFromUrl();
                }
            });
        } catch (e) {
            console.warn('OneSignal init failed', e);
        }
    });
}
initOneSignalPush();
@endif

initAgendaDate();
updateTodayButton();
loadAgenda().then(() => handleDeepLinkFromUrl());
loadTechnicianCashSummary();
updateMainHeaderOffset();
window.addEventListener('resize', updateMainHeaderOffset);

document.getElementById('search-input').addEventListener('input', onSearchInput);
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    if (!document.getElementById('search-panel').classList.contains('hidden')) {
        closeSearchPanel();
        return;
    }
    if (document.getElementById('side-menu').classList.contains('is-open')) {
        closeSideMenu();
        return;
    }
    if (!document.getElementById('recovery-sheet').classList.contains('hidden')) {
        closeRecoverySheet();
        return;
    }
    if (!document.getElementById('recovery-payment-modal').classList.contains('hidden')) {
        closeRecoveryPaymentModal();
        return;
    }
    if (!document.getElementById('invoice-payment-modal').classList.contains('hidden')) {
        closeInvoicePaymentModal();
        return;
    }
    if (!document.getElementById('invoice-late-payment-modal').classList.contains('hidden')) {
        closeInvoiceLatePaymentModal();
    }
});
</script>

@if($mapsApiKey)
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ $mapsApiKey }}&libraries=places&callback=onGoogleMapsLoaded">
</script>
@endif
</body>
</html>

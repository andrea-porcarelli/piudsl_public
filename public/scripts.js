// PiùDSL Client Area Management System
class ClientAreaManager {
    constructor() {
        this.isLoggedIn = false;
        this.currentUser = null;
        this.services = [];
        this.invoices = [];
        this.supportTickets = [];
        this.notifications = [];
        
        this.init();
    }

    init() {
        // Check for existing session
        this.checkExistingSession();
        
        // Initialize event listeners
        this.setupEventListeners();
        
        // Load user preferences
        this.loadUserPreferences();
    }

    checkExistingSession() {
        const session = localStorage.getItem('piudsl_session');
        if (session) {
            try {
                const sessionData = JSON.parse(session);
                if (sessionData.expires > Date.now()) {
                    this.currentUser = sessionData.user;
                    this.isLoggedIn = true;
                    this.loadUserData();
                }
            } catch (error) {
                console.error('Invalid session data:', error);
                localStorage.removeItem('piudsl_session');
            }
        }
    }

    setupEventListeners() {
        // Login form submission
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'login-form') {
                this.handleLogin(e);
            }
        });

        // Logout functionality
        document.addEventListener('click', (e) => {
            if (e.target.dataset.action === 'logout') {
                this.handleLogout();
            }
        });

        // Service management
        document.addEventListener('click', (e) => {
            if (e.target.dataset.action) {
                this.handleServiceAction(e.target.dataset.action, e.target.dataset.serviceId);
            }
        });
    }

    async handleLogin(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const credentials = {
            email: formData.get('email'),
            password: formData.get('password'),
            remember: formData.get('remember') === 'on'
        };

        // Show loading state
        const submitButton = event.target.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.textContent = 'Accesso in corso...';
        submitButton.disabled = true;

        try {
            // Simulate API call
            const response = await this.simulateLogin(credentials);
            
            if (response.success) {
                this.currentUser = response.user;
                this.isLoggedIn = true;
                
                // Save session
                const sessionData = {
                    user: response.user,
                    expires: Date.now() + (credentials.remember ? 30 * 24 * 60 * 60 * 1000 : 24 * 60 * 60 * 1000)
                };
                localStorage.setItem('piudsl_session', JSON.stringify(sessionData));
                
                // Load user data
                await this.loadUserData();
                
                // Switch to dashboard
                this.showDashboard();
                
                // Show success notification
                this.showNotification('Accesso effettuato con successo!', 'success');
                
            } else {
                this.showNotification(response.message || 'Credenziali non valide', 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showNotification('Errore durante l\'accesso. Riprova più tardi.', 'error');
        } finally {
            submitButton.textContent = originalText;
            submitButton.disabled = false;
        }
    }

    async simulateLogin(credentials) {
        // Simulate API delay
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        // Demo credentials
        const validCredentials = {
            'mario.rossi@email.com': 'password123',
            'cliente@esempio.com': 'demo123',
            'test@piudsl.it': 'test123'
        };

        if (validCredentials[credentials.email] === credentials.password) {
            return {
                success: true,
                user: {
                    id: '12345',
                    name: 'Mario',
                    surname: 'Rossi',
                    email: credentials.email,
                    phone: '+39 123 456 7890',
                    customerCode: 'PD12345',
                    avatar: 'https://ui-avatars.com/api/?name=Mario+Rossi&background=0ea5e9&color=fff'
                }
            };
        }

        return {
            success: false,
            message: 'Email o password non corretti'
        };
    }

    async loadUserData() {
        if (!this.currentUser) return;

        try {
            // Simulate loading services
            this.services = await this.loadServices();
            
            // Simulate loading invoices
            this.invoices = await this.loadInvoices();
            
            // Simulate loading support tickets
            this.supportTickets = await this.loadSupportTickets();
            
            // Update dashboard if visible
            if (document.getElementById('client-dashboard').style.display !== 'none') {
                this.updateDashboard();
            }
            
        } catch (error) {
            console.error('Error loading user data:', error);
            this.showNotification('Errore nel caricamento dei dati', 'error');
        }
    }

    async loadServices() {
        await new Promise(resolve => setTimeout(resolve, 800));
        
        return [
            {
                id: 'srv_001',
                name: 'Internet Fibra 100MB',
                type: 'internet',
                status: 'active',
                speed: '100 Mbps',
                monthlyFee: 28.99,
                nextBilling: '2024-03-15',
                installation: '2023-06-20',
                ipAddress: '93.42.15.123'
            },
            {
                id: 'srv_002',
                name: 'VoIP Business',
                type: 'voip',
                status: 'active',
                numbers: ['0823180001', '0823180002'],
                monthlyFee: 15.99,
                nextBilling: '2024-03-15'
            }
        ];
    }

    async loadInvoices() {
        await new Promise(resolve => setTimeout(resolve, 600));
        
        return [
            {
                id: 'inv_001',
                number: '2024-001',
                date: '2024-02-15',
                dueDate: '2024-03-15',
                amount: 44.98,
                status: 'paid',
                pdfUrl: '#'
            },
            {
                id: 'inv_002',
                number: '2024-002',
                date: '2024-01-15',
                dueDate: '2024-02-15',
                amount: 44.98,
                status: 'paid',
                pdfUrl: '#'
            },
            {
                id: 'inv_003',
                number: '2023-024',
                date: '2023-12-15',
                dueDate: '2024-01-15',
                amount: 44.98,
                status: 'paid',
                pdfUrl: '#'
            }
        ];
    }

    async loadSupportTickets() {
        await new Promise(resolve => setTimeout(resolve, 400));
        
        return [
            {
                id: 'tick_001',
                subject: 'Velocità connessione ridotta',
                status: 'resolved',
                priority: 'medium',
                created: '2024-02-10',
                lastUpdate: '2024-02-12',
                messages: 3
            }
        ];
    }

    showDashboard() {
        document.getElementById('login-form').classList.add('hidden');
        document.getElementById('client-dashboard').classList.remove('hidden');
        this.updateDashboard();
    }

    updateDashboard() {
        if (!this.currentUser) return;

        // Update user info
        const userNameEl = document.querySelector('#client-dashboard .user-name');
        if (userNameEl) {
            userNameEl.textContent = `Benvenuto, ${this.currentUser.name}!`;
        }

        // Update stats
        this.updateDashboardStats();
        
        // Update recent activities
        this.updateRecentActivities();
    }

    updateDashboardStats() {
        const activeServices = this.services.filter(s => s.status === 'active').length;
        const totalMonthly = this.services.reduce((sum, s) => sum + s.monthlyFee, 0);
        const unpaidInvoices = this.invoices.filter(i => i.status !== 'paid').length;
        const openTickets = this.supportTickets.filter(t => t.status === 'open').length;

        // Update stats cards if they exist
        const statsContainer = document.querySelector('#dashboard-stats');
        if (statsContainer) {
            statsContainer.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-brand-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-brand-600">${activeServices}</div>
                        <div class="text-sm text-gray-600">Servizi Attivi</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-green-600">€${totalMonthly.toFixed(2)}</div>
                        <div class="text-sm text-gray-600">Costo Mensile</div>
                    </div>
                    <div class="bg-yellow-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-600">${unpaidInvoices}</div>
                        <div class="text-sm text-gray-600">Fatture in Sospeso</div>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-purple-600">${openTickets}</div>
                        <div class="text-sm text-gray-600">Ticket Aperti</div>
                    </div>
                </div>
            `;
        }
    }

    updateRecentActivities() {
        const activitiesContainer = document.querySelector('#recent-activities');
        if (!activitiesContainer) return;

        const recentInvoices = this.invoices.slice(0, 3);
        const recentTickets = this.supportTickets.slice(0, 2);

        let activitiesHTML = '<div class="space-y-3">';
        
        // Recent invoices
        recentInvoices.forEach(invoice => {
            const statusClass = invoice.status === 'paid' ? 'text-green-600' : 'text-yellow-600';
            const statusText = invoice.status === 'paid' ? 'Pagata' : 'In Sospeso';
            
            activitiesHTML += `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <i data-feather="file-text" class="w-4 h-4 text-gray-500"></i>
                        <div>
                            <div class="text-sm font-medium">Fattura ${invoice.number}</div>
                            <div class="text-xs text-gray-500">${invoice.date}</div>
                        </div>
                    </div>
                    <div class="text-sm ${statusClass} font-medium">${statusText}</div>
                </div>
            `;
        });

        // Recent tickets
        recentTickets.forEach(ticket => {
            const statusClass = ticket.status === 'resolved' ? 'text-green-600' : 
                               ticket.status === 'open' ? 'text-yellow-600' : 'text-gray-600';
            const statusText = ticket.status === 'resolved' ? 'Risolto' : 
                              ticket.status === 'open' ? 'Aperto' : 'Chiuso';
            
            activitiesHTML += `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <i data-feather="headphones" class="w-4 h-4 text-gray-500"></i>
                        <div>
                            <div class="text-sm font-medium">${ticket.subject}</div>
                            <div class="text-xs text-gray-500">${ticket.created}</div>
                        </div>
                    </div>
                    <div class="text-sm ${statusClass} font-medium">${statusText}</div>
                </div>
            `;
        });

        activitiesHTML += '</div>';
        activitiesContainer.innerHTML = activitiesHTML;
        
        // Re-initialize feather icons
        feather.replace();
    }

    handleLogout() {
        // Clear session
        localStorage.removeItem('piudsl_session');
        
        // Reset state
        this.isLoggedIn = false;
        this.currentUser = null;
        this.services = [];
        this.invoices = [];
        this.supportTickets = [];
        
        // Return to login form
        document.getElementById('login-form').classList.remove('hidden');
        document.getElementById('client-dashboard').classList.add('hidden');
        
        // Show notification
        this.showNotification('Disconnesso con successo', 'info');
    }

    handleServiceAction(action, serviceId) {
        const service = this.services.find(s => s.id === serviceId);
        if (!service) return;

        switch (action) {
            case 'suspend':
                this.suspendService(serviceId);
                break;
            case 'resume':
                this.resumeService(serviceId);
                break;
            case 'upgrade':
                this.showUpgradeOptions(serviceId);
                break;
            case 'details':
                this.showServiceDetails(serviceId);
                break;
        }
    }

    async suspendService(serviceId) {
        try {
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            const service = this.services.find(s => s.id === serviceId);
            if (service) {
                service.status = 'suspended';
                this.updateDashboard();
                this.showNotification('Servizio sospeso con successo', 'success');
            }
        } catch (error) {
            this.showNotification('Errore nella sospensione del servizio', 'error');
        }
    }

    async resumeService(serviceId) {
        try {
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            const service = this.services.find(s => s.id === serviceId);
            if (service) {
                service.status = 'active';
                this.updateDashboard();
                this.showNotification('Servizio riattivato con successo', 'success');
            }
        } catch (error) {
            this.showNotification('Errore nella riattivazione del servizio', 'error');
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <i data-feather="${
                    type === 'success' ? 'check-circle' :
                    type === 'error' ? 'x-circle' :
                    type === 'warning' ? 'alert-triangle' :
                    'info'
                }" class="w-5 h-5"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 hover:bg-black hover:bg-opacity-20 rounded p-1">
                    <i data-feather="x" class="w-4 h-4"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        feather.replace();
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.parentElement.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }

    loadUserPreferences() {
        const preferences = localStorage.getItem('piudsl_preferences');
        if (preferences) {
            try {
                const prefs = JSON.parse(preferences);
                // Apply preferences (theme, language, etc.)
                this.applyPreferences(prefs);
            } catch (error) {
                console.error('Invalid preferences data:', error);
            }
        }
    }

    applyPreferences(preferences) {
        // Theme
        if (preferences.theme === 'dark') {
            document.documentElement.classList.add('dark');
        }
        
        // Language
        if (preferences.language) {
            document.documentElement.lang = preferences.language;
        }
        
        // Notifications
        this.notificationPreferences = preferences.notifications || {};
    }

    savePreferences(preferences) {
        localStorage.setItem('piudsl_preferences', JSON.stringify(preferences));
        this.applyPreferences(preferences);
    }
}

// Speed Test Functionality
class SpeedTest {
    constructor() {
        this.isRunning = false;
        this.results = {
            download: 0,
            upload: 0,
            ping: 0
        };
    }

    async runTest() {
        if (this.isRunning) return;
        
        this.isRunning = true;
        this.showProgress();
        
        try {
            // Ping test
            await this.testPing();
            
            // Download test
            await this.testDownload();
            
            // Upload test
            await this.testUpload();
            
            this.showResults();
        } catch (error) {
            console.error('Speed test error:', error);
            this.showError();
        } finally {
            this.isRunning = false;
        }
    }

    async testPing() {
        const start = performance.now();
        
        // Simulate ping test with random realistic values
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        const end = performance.now();
        this.results.ping = Math.round(Math.random() * 20 + 5); // 5-25ms
        
        this.updateProgress('ping', this.results.ping);
    }

    async testDownload() {
        const targetSpeed = 95 + Math.random() * 10; // 95-105 Mbps
        
        for (let i = 0; i <= 100; i += 2) {
            await new Promise(resolve => setTimeout(resolve, 50));
            const currentSpeed = (targetSpeed * i) / 100;
            this.results.download = Math.round(currentSpeed * 10) / 10;
            this.updateProgress('download', this.results.download);
        }
    }

    async testUpload() {
        const targetSpeed = 18 + Math.random() * 5; // 18-23 Mbps
        
        for (let i = 0; i <= 100; i += 3) {
            await new Promise(resolve => setTimeout(resolve, 60));
            const currentSpeed = (targetSpeed * i) / 100;
            this.results.upload = Math.round(currentSpeed * 10) / 10;
            this.updateProgress('upload', this.results.upload);
        }
    }

    showProgress() {
        // Implementation would update UI with progress indicators
        console.log('Speed test started...');
    }

    updateProgress(type, value) {
        // Implementation would update specific progress bars
        console.log(`${type}: ${value}`);
    }

    showResults() {
        console.log('Speed test results:', this.results);
        // Implementation would display results in UI
    }

    showError() {
        console.error('Speed test failed');
        // Implementation would show error message
    }
}

// Support System
class SupportSystem {
    constructor() {
        this.tickets = [];
        this.chatMessages = [];
    }

    async createTicket(ticketData) {
        try {
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            const ticket = {
                id: `tick_${Date.now()}`,
                ...ticketData,
                status: 'open',
                created: new Date().toISOString(),
                messages: []
            };
            
            this.tickets.push(ticket);
            return ticket;
        } catch (error) {
            throw new Error('Errore nella creazione del ticket');
        }
    }

    async sendMessage(ticketId, message) {
        try {
            await new Promise(resolve => setTimeout(resolve, 500));
            
            const ticket = this.tickets.find(t => t.id === ticketId);
            if (ticket) {
                ticket.messages.push({
                    id: Date.now(),
                    message,
                    timestamp: new Date().toISOString(),
                    sender: 'customer'
                });
                
                // Simulate automatic response
                setTimeout(() => {
                    ticket.messages.push({
                        id: Date.now() + 1,
                        message: 'Grazie per il tuo messaggio. Il nostro team ti risponderà al più presto.',
                        timestamp: new Date().toISOString(),
                        sender: 'support'
                    });
                }, 2000);
            }
            
            return true;
        } catch (error) {
            throw new Error('Errore nell\'invio del messaggio');
        }
    }

    startLiveChat() {
        // Implementation for live chat functionality
        console.log('Starting live chat...');
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize client area manager
    window.clientAreaManager = new ClientAreaManager();
    
    // Initialize speed test
    window.speedTest = new SpeedTest();
    
    // Initialize support system
    window.supportSystem = new SupportSystem();
    
    // Add global helper functions
    window.openClientArea = function() {
        document.getElementById('client-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };
    
    window.closeClientArea = function() {
        document.getElementById('client-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    };
    
    // Enhanced form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', validateInput);
            input.addEventListener('input', clearError);
        });
    });
});

// Utility functions
function validateInput(event) {
    const input = event.target;
    const value = input.value.trim();
    
    // Remove existing error
    clearError(event);
    
    let isValid = true;
    let errorMessage = '';
    
    // Required validation
    if (input.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Questo campo è obbligatorio';
    }
    
    // Email validation
    if (input.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Inserisci un indirizzo email valido';
        }
    }
    
    // Phone validation
    if (input.type === 'tel' && value) {
        const phoneRegex = /^\+?[\d\s\-\(\)]+$/;
        if (!phoneRegex.test(value) || value.replace(/\D/g, '').length < 10) {
            isValid = false;
            errorMessage = 'Inserisci un numero di telefono valido';
        }
    }
    
    if (!isValid) {
        showInputError(input, errorMessage);
    }
    
    return isValid;
}

function clearError(event) {
    const input = event.target;
    const errorElement = input.parentElement.querySelector('.error-message');
    if (errorElement) {
        errorElement.remove();
    }
    input.classList.remove('border-red-500');
}

function showInputError(input, message) {
    input.classList.add('border-red-500');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'error-message text-red-500 text-sm mt-1';
    errorElement.textContent = message;
    
    input.parentElement.appendChild(errorElement);
}

// Smooth scrolling polyfill for older browsers
if (!('scrollBehavior' in document.documentElement.style)) {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/smooth-scroll@16.1.3/dist/smooth-scroll.polyfills.min.js';
    document.head.appendChild(script);
}

// Analytics and tracking (placeholder)
function trackEvent(category, action, label) {
    // Implementation for analytics tracking
    console.log(`Track: ${category} - ${action} - ${label}`);
}

// Performance monitoring
const observer = new PerformanceObserver((list) => {
    const entries = list.getEntries();
    entries.forEach((entry) => {
        console.log(`Performance: ${entry.name} - ${entry.duration}ms`);
    });
});

observer.observe({ entryTypes: ['navigation', 'measure'] });

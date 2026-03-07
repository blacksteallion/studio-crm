<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <title>Login - Studio CRM</title>
    
    <link rel="manifest" href="{{ asset('manifest.json') }}?v=2">
    
    <meta name="theme-color" content="#ffffff">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: dark)">
    
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Studio CRM">
    
    <link href="https://fonts.googleapis.com/css2?family=Satoshi:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Satoshi', sans-serif; background-color: #f8fafc; }</style>
</head>
<body class="flex items-center justify-center min-h-screen p-6">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        
        <div class="p-8 text-center">
            <div class="flex justify-center mb-6">
                @php 
                    $loginLogo = \App\Models\Setting::get('company_logo'); 
                    $companyName = \App\Models\Setting::get('company_name', 'TC Studio');
                @endphp

                @if($loginLogo)
                    <img src="{{ asset('storage/' . $loginLogo) }}" alt="{{ $companyName }}" class="h-16 w-auto object-contain">
                @else
                    <div class="inline-flex items-center gap-3">
                        <div class="bg-blue-600 text-white p-3 rounded-xl shadow-lg shadow-blue-200">
                            <i class="fas fa-chart-pie text-2xl"></i>
                        </div>
                        <span class="text-3xl font-black text-slate-800 tracking-tight">{{ $companyName }}</span>
                    </div>
                @endif
            </div>

            <h2 class="text-2xl font-bold text-slate-800 mb-2">Welcome Back!</h2>
            <p class="text-slate-500 text-sm mb-8">Please sign in to continue to your dashboard.</p>

            @if($errors->any() || session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3">
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                    <div class="text-left">
                        <h4 class="text-sm font-bold text-red-800">Authentication Failed</h4>
                        <p class="text-xs text-red-600 mt-1">{{ session('error') ?? $errors->first() }}</p>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-start gap-3">
                    <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                    <div class="text-left">
                        <h4 class="text-sm font-bold text-green-800">Success</h4>
                        <p class="text-xs text-green-600 mt-1">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="text-left">
                @csrf
                <div class="mb-5">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Email Address</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-gray-400"><i class="far fa-envelope"></i></span>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white transition-all placeholder-gray-400 font-medium"
                            placeholder="name@company.com">
                    </div>
                </div>

                <div class="mb-8">
                    <div class="flex justify-between items-center mb-2 ml-1">
                        <label class="block text-xs font-bold text-slate-500 uppercase">Password</label>
                        <a href="{{ route('password.request') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline transition">Forgot Password?</a>
                    </div>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-gray-400"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" required 
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white transition-all placeholder-gray-400 font-medium"
                            placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-200 transition-all transform hover:scale-[1.02] active:scale-95">
                    Sign In
                </button>
            </form>

            <div id="installPwaContainer" class="hidden mt-6 pt-6 border-t border-gray-100">
                <button type="button" id="installPwaBtn" class="w-full bg-white hover:bg-gray-50 text-slate-700 font-bold py-3 rounded-xl border-2 border-slate-200 shadow-sm transition-all transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-3">
                    <i class="fas fa-download text-blue-600 text-lg"></i>
                    <span id="installBtnText">Install Mobile App</span>
                </button>
                <p id="installPwaDesc" class="text-[11px] text-gray-400 mt-2 font-medium">Get the full app experience on your home screen.</p>
            </div>

            <div id="manualInstallPrompt" class="hidden mt-6 pt-6 border-t border-gray-100 text-left">
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <i id="manualDeviceIcon" class="fas fa-mobile-alt text-blue-600 text-lg"></i>
                        <h4 class="font-bold text-blue-800 text-sm">How to Install</h4>
                    </div>
                    <p id="manualInstallText" class="text-xs text-blue-700 leading-relaxed"></p>
                </div>
            </div>

        </div>
        
        <div class="bg-gray-50 p-4 text-center border-t border-gray-100">
            <p class="text-xs text-gray-400">
                &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
        if (isStandalone) {
            if (!sessionStorage.getItem('pwa_token_refreshed')) {
                sessionStorage.setItem('pwa_token_refreshed', 'true');
                window.location.reload(true);
            }
        }

        let deferredPrompt = null;
        const installContainer = document.getElementById('installPwaContainer');
        const installBtn = document.getElementById('installPwaBtn');
        const manualPrompt = document.getElementById('manualInstallPrompt');
        const manualText = document.getElementById('manualInstallText');
        const manualIcon = document.getElementById('manualDeviceIcon');

        const isIos = () => {
            const userAgent = window.navigator.userAgent.toLowerCase();
            return /iphone|ipad|ipod/.test(userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        };
        const isMobile = () => {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || isIos();
        };

        if (isMobile() && !isStandalone) {
            installContainer.classList.remove('hidden');
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (!isStandalone) {
                installContainer.classList.remove('hidden');
            }
        });

        installBtn.addEventListener('click', async () => {
            if (isIos()) {
                manualIcon.className = "fab fa-apple text-blue-600 text-lg";
                manualText.innerHTML = 'To install this app, tap the <strong>Share</strong> icon <i class="fas fa-external-link-square-alt mx-1 opacity-70"></i> at the bottom of Safari, scroll down, and tap <strong>"Add to Home Screen"</strong> <i class="fas fa-plus-square mx-1 opacity-70"></i>.';
                installBtn.classList.add('hidden');
                document.getElementById('installPwaDesc').classList.add('hidden');
                manualPrompt.classList.remove('hidden');
                return;
            }

            if (deferredPrompt !== null) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    installContainer.classList.add('hidden');
                }
                deferredPrompt = null;
            } else {
                manualIcon.className = "fab fa-android text-blue-600 text-lg";
                manualText.innerHTML = 'To install, tap the <strong>Browser Menu</strong> (3 dots at the top right) and select <strong>"Install app"</strong> or <strong>"Add to Home screen"</strong>.';
                installBtn.classList.add('hidden');
                document.getElementById('installPwaDesc').classList.add('hidden');
                manualPrompt.classList.remove('hidden');
            }
        });

        window.addEventListener('appinstalled', () => {
            installContainer.classList.add('hidden');
            manualPrompt.classList.add('hidden');
            deferredPrompt = null;
        });

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
</body>
</html>
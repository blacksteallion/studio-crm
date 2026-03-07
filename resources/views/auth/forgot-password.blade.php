<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Studio CRM</title>
    <link href="https://fonts.googleapis.com/css2?family=Satoshi:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Satoshi', sans-serif; background-color: #f8fafc; }</style>
</head>
<body class="flex items-center justify-center min-h-screen p-6">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        
        <div class="p-8 text-center">
            
            {{-- DYNAMIC LOGO LOGIC (Same as Login Page) --}}
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

            <h2 class="text-2xl font-bold text-slate-800 mb-2">Reset Password</h2>
            <p class="text-slate-500 text-sm mb-8">Enter your registered email address, and we'll send you a link to reset your password.</p>

            @if(session('status'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-start gap-3">
                    <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                    <div class="text-left">
                        <h4 class="text-sm font-bold text-green-800">Email Sent</h4>
                        <p class="text-xs text-green-600 mt-1">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3">
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                    <div class="text-left">
                        <h4 class="text-sm font-bold text-red-800">Error</h4>
                        <p class="text-xs text-red-600 mt-1">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="text-left">
                @csrf
                <div class="mb-8">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Email Address</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-gray-400"><i class="far fa-envelope"></i></span>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white transition-all placeholder-gray-400 font-medium"
                            placeholder="name@company.com">
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-200 transition-all transform hover:scale-[1.02] active:scale-95 mb-4">
                    Send Reset Link
                </button>

                <a href="{{ route('login') }}" class="block text-center text-sm font-bold text-gray-500 hover:text-blue-600 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Login
                </a>
            </form>
        </div>
    </div>

</body>
</html>
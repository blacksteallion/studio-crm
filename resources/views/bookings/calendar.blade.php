@extends('layouts.app')
@section('header', 'Booking Calendar')

@section('content')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-bold text-slate-800">Calendar View</h2>
    <form method="GET" action="{{ route('bookings.calendar') }}" class="flex items-center gap-2">
        <label class="text-sm font-bold text-gray-500 hidden sm:block">Filter Location:</label>
        <select name="location_id" onchange="this.form.submit()" class="bg-white border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block px-3 py-2 shadow-sm font-semibold cursor-pointer">
            @if(Auth::user()->hasRole('Super Admin'))
            <option value="all" {{ request('location_id', session('active_location_id')) == 'all' ? 'selected' : '' }}>🌐 All Locations</option>
            @endif
            @foreach($locations as $loc)
                <option value="{{ $loc->id }}" {{ request('location_id', session('active_location_id')) == $loc->id ? 'selected' : '' }}>📍 {{ $loc->name }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="rounded-2xl border border-gray-200 bg-white shadow-sm h-full relative">
    <div class="p-6">
        <div id="calendar" class="min-h-[700px]"></div>
    </div>
</div>

<div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden backdrop-blur-sm transition-opacity" onclick="closeModal(event)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 transform transition-all scale-100" onclick="event.stopPropagation()">
        
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 id="modalTitle" class="text-xl font-bold text-slate-800"></h3>
                <span id="modalStatus" class="inline-block px-2.5 py-0.5 rounded text-xs font-bold mt-1"></span>
            </div>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="space-y-4">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 text-gray-400 w-5"><i class="far fa-clock"></i></div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Date & Time</p>
                    <p id="modalTime" class="text-slate-700 font-medium text-sm"></p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="mt-0.5 text-red-400 w-5"><i class="fas fa-map-marker-alt"></i></div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Studio Location</p>
                    <p id="modalLocation" class="text-slate-700 font-medium text-sm"></p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="mt-0.5 text-gray-400 w-5"><i class="fas fa-user-tie"></i></div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Assigned Staff</p>
                    <p id="modalStaff" class="text-slate-700 font-medium text-sm"></p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="mt-0.5 text-gray-400 w-5"><i class="fas fa-phone"></i></div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">Customer Mobile</p>
                    <p id="modalMobile" class="text-slate-700 font-medium text-sm font-mono"></p>
                </div>
            </div>

            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase mb-1">Notes</p>
                <p id="modalNotes" class="text-slate-600 text-sm italic"></p>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
            <button onclick="closeModal()" class="px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-100 transition">Close</button>
            
            @can('edit bookings')
            <a id="modalEditBtn" href="#" class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md transition flex items-center gap-2">
                <i class="fas fa-pencil-alt"></i> Edit Booking
            </a>
            @endcan
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridDay',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: @json($events),

            eventContent: function(arg) {
                let company = arg.event.extendedProps.company_name;
                
                // Format Time: 11:00 AM - 01:00 PM
                let start = arg.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                let end = arg.event.end ? arg.event.end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '';
                let timeRange = start + (end ? ' - ' + end : '');

                // Create DOM Elements
                let container = document.createElement('div');
                container.className = 'flex flex-col leading-tight overflow-hidden';

                let titleEl = document.createElement('div');
                titleEl.className = 'font-bold text-xs truncate';
                titleEl.innerText = company;

                let timeEl = document.createElement('div');
                timeEl.className = 'text-[10px] opacity-90 truncate';
                timeEl.innerText = timeRange;

                container.appendChild(titleEl);
                container.appendChild(timeEl);

                return { domNodes: [container] };
            },
            
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                
                const props = info.event.extendedProps;
                
                // Populate Modal Data
                document.getElementById('modalTitle').innerText = props.customer_name;
                document.getElementById('modalMobile').innerText = props.customer_mobile;
                document.getElementById('modalStaff').innerText = props.staff;
                document.getElementById('modalLocation').innerText = props.location; // <--- ADDED JS TO MODAL
                document.getElementById('modalNotes').innerText = props.notes;
                
                // Safety check for permissions: if button exists, apply URL
                const editBtn = document.getElementById('modalEditBtn');
                if (editBtn) {
                    editBtn.href = props.edit_url;
                }

                // Format Time Range for Modal
                const start = info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                const end = info.event.end ? info.event.end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '';
                const date = info.event.start.toLocaleDateString([], {weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'});
                document.getElementById('modalTime').innerText = `${date} • ${start} - ${end}`;

                // Status Badge Color
                const statusEl = document.getElementById('modalStatus');
                statusEl.innerText = props.status;
                statusEl.className = 'inline-block px-2.5 py-0.5 rounded-full text-xs font-bold mt-1';
                
                if(props.status === 'Completed') statusEl.classList.add('bg-green-100', 'text-green-700');
                else if(props.status === 'Cancelled') statusEl.classList.add('bg-red-100', 'text-red-700');
                else if(props.status === 'No Show') statusEl.classList.add('bg-yellow-100', 'text-yellow-700');
                else statusEl.classList.add('bg-blue-100', 'text-blue-700');

                // Show Modal
                document.getElementById('eventModal').classList.remove('hidden');
            },

            eventTimeFormat: { 
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            eventDidMount: function(info) {
                info.el.style.borderRadius = '6px';
                info.el.style.padding = '4px';
                info.el.style.border = 'none';
                info.el.style.boxShadow = '0 1px 2px rgba(0,0,0,0.1)';
            }
        });
        calendar.render();
    });

    function closeModal() {
        document.getElementById('eventModal').classList.add('hidden');
    }
</script>

<style>
    /* Spacing Fix for Buttons */
    .fc-header-toolbar .fc-button-group > .fc-button {
        margin-right: 2px;
    }
    .fc-header-toolbar .fc-button {
        margin-right: 8px !important;
        border-radius: 8px !important;
        text-transform: capitalize;
        font-weight: 600;
        border: none !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    
    .fc-header-toolbar .fc-button-group > .fc-button:last-child {
        margin-right: 8px !important; 
    }

    /* Colors matching app theme */
    .fc-button-primary { 
        background-color: #3b82f6 !important; 
    }
    .fc-button-primary:hover { 
        background-color: #2563eb !important; 
    }
    .fc-button-active {
        background-color: #1d4ed8 !important;
    }

    .fc-today-button {
        margin-right: 0 !important;
    }

    .fc-toolbar-title { 
        font-size: 1.5rem !important; 
        font-weight: 800; 
        color: #1e293b; 
    }
    
    .fc-col-header-cell-cushion { 
        color: #64748b; 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        padding: 12px 0 !important; 
    }
    
    .fc-daygrid-day-number {
        color: #475569;
        font-weight: 600;
        margin: 4px;
    }
</style>
@endsection
@extends('layouts.app')

@section('title', 'Log Aktivitas & Audit')
@section('page_title', 'Log Aktivitas & Audit Keamanan Sistem')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h2 class="text-base font-bold text-slate-900">Rekaman Audit Aktivitas Sistem</h2>
            <p class="text-xs text-slate-500 mt-0.5">Memantau seluruh aktivitas sensitif: login, buka/tutup sesi QR, penolakan lokasi GPS, perubahan akun, dan tindakan admin.</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
            <i class="fa-solid fa-shield-check mr-1.5"></i> Audit Trail Aktif
        </span>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.audit_logs') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Cari Aksi / Event</label>
                <input type="text" name="action" value="{{ request('action') }}" placeholder="Contoh: login, scan, qr..."
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div>
                <label class="block text-slate-500 mb-1 font-semibold">Tanggal Log</label>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-600 focus:outline-none">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-900 transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.audit_logs') }}" class="py-2 px-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Tindakan / Event</th>
                        <th class="py-3 px-4">Detail Aktivitas</th>
                        <th class="py-3 px-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($logs as $l)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 text-slate-500 whitespace-nowrap font-sans">
                                {{ $l->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="py-3 px-4 font-sans font-bold text-slate-800">
                                {{ $l->user->name ?? 'Sistem / Anonim' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $l->action }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-sans text-slate-600 max-w-md break-words">
                                {{ is_array($l->details) ? json_encode($l->details) : $l->details }}
                            </td>
                            <td class="py-3 px-4 text-slate-400 text-[11px]">{{ $l->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400 font-sans">Belum ada rekaman audit log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection

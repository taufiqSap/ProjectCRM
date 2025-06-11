<x-filament::page>
    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter Form --}}
    <div class="mb-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm border rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-7 7v4l-4 2v-6l-7-7V4z" />
                </svg>
                Filter Tanggal
            </h2>
            <form wire:submit.prevent="submit" class="space-y-6">
                {{ $this->form }}

                <div class="flex gap-3">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 2v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Terapkan Filter
                    </button>

                    <button type="button" 
                            wire:click="resetFilter"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Dashboard Widgets --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="col-span-1 md:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border p-4">
            @livewire(\App\Filament\Widgets\AStatsOverview::class)
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border p-4">
            @livewire(\App\Filament\Widgets\ChartHarian::class)
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border p-4">
            @livewire(\App\Filament\Widgets\ChartBulanan::class)
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border p-4">
            @livewire(\App\Filament\Widgets\ChartATahunan::class)
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border p-4">
            @livewire(\App\Filament\Widgets\ChartTeknisi::class)
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border p-4">
            @livewire(\App\Filament\Widgets\ChartService::class)
        </div>
        {{-- Grafik Jumlah Service Tahunan --}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border p-4">
    @livewire(\App\Filament\Widgets\ChartServiceTahunan::class)
</div>

    </div>
</x-filament::page>

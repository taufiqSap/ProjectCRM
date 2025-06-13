<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class Dashboard extends Page implements Forms\Contracts\HasForms
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    use Forms\Concerns\InteractsWithForms;

    protected static string $view = 'filament.pages.dashboard';

    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->form->fill([
            'startDate' => session('dashboard_start_date'),
            'endDate' => session('dashboard_end_date'),
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Filter Tanggal')
                ->description('Pilih rentang tanggal untuk menampilkan data pada dashboard')
                ->schema([
                    DatePicker::make('startDate')
                        ->label('Dari Tanggal')
                        ->native(false),

                    DatePicker::make('endDate')
                        ->label('Sampai Tanggal')
                        ->native(false),
                ])
                ->columns(2)
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        if (!$data['startDate'] || !$data['endDate']) {
            session()->flash('error', 'Tanggal awal dan akhir harus diisi.');
            return;
        }

        if ($data['startDate'] > $data['endDate']) {
            session()->flash('error', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.');
            return;
        }

        Session::put('dashboard_start_date', $data['startDate']);
        Session::put('dashboard_end_date', $data['endDate']);

        session()->flash('success', 'Filter tanggal berhasil diterapkan!');
        $this->redirect(request()->header('Referer') ?? route('filament.pages.dashboard'));
    }

    public function resetFilter(): void
    {
        Session::forget(['dashboard_start_date', 'dashboard_end_date']);

        $this->form->fill([
            'startDate' => null,
            'endDate' => null,
        ]);

        session()->flash('success', 'Filter telah direset. Data tidak akan ditampilkan.');
        $this->redirect(request()->header('Referer') ?? route('filament.pages.dashboard'));
    }

    public function getTitle(): string
    {
        $start = session('dashboard_start_date');
        $end = session('dashboard_end_date');

        if ($start && $end) {
            return 'Dashboard (' . Carbon::parse($start)->format('d M Y') . ' - ' . Carbon::parse($end)->format('d M Y') . ')';
        }

        return 'Dashboard (-)';
    }
}

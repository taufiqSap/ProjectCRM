<x-filament::page>
    {{-- Render form dan FileUpload bawaan Filament --}}
    {{ $this->form }}

    {{-- Tombol yang memanggil method submit() --}}
    <div class="mt-4">
        <x-filament::button wire:click="submit" type="button">
            Import Excel
        </x-filament::button>
    </div>
</x-filament::page>

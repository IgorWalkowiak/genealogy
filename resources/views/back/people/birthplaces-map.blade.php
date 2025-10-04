@section('title')
    &vert; {{ __('app.birthplaces_map') }}
@endsection

<x-app-layout>
    <x-slot name="heading">
        {{ __('app.birthplaces_map') }}
    </x-slot>

    <div class="w-full p-2 space-y-5">
        <livewire:people.birthplaces-map />
    </div>
</x-app-layout>

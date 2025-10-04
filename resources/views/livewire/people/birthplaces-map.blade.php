<div class="w-full">
    {{-- Pasek postępu geokodowania --}}
    <div id="geocoding-progress-bar" class="mb-6 bg-white dark:bg-neutral-700 rounded-lg shadow p-4 hidden">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">
                    Ładowanie mapy
                </h3>
                <p id="geocoding-status" class="text-sm text-neutral-600 dark:text-neutral-400">
                    Przygotowywanie...
                </p>
            </div>
            <div class="text-right">
                <div id="geocoding-percentage" class="text-2xl font-bold text-primary-600 dark:text-primary-400">0%</div>
                <div id="geocoding-count" class="text-xs text-neutral-500 dark:text-neutral-400">0 / 0</div>
            </div>
        </div>
        <div class="w-full bg-neutral-200 dark:bg-neutral-600 rounded-full h-3 overflow-hidden">
            <div id="geocoding-progress-fill" class="bg-gradient-to-r from-primary-500 to-primary-600 h-3 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
        </div>
        <div class="mt-2 text-xs text-neutral-500 dark:text-neutral-400 text-center">
            💡 Geokodowanie trwa około 1 sekundy na miejsce (limit API). Współrzędne zostaną zapisane i następne ładowanie będzie szybkie!
        </div>
    </div>

    {{-- Informacje o statystykach --}}
    <div class="mb-6 p-4 bg-white dark:bg-neutral-700 rounded-lg shadow">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-neutral-800 dark:text-neutral-200">Statystyki</h3>
            <x-ts-button wire:click="openPlaceModal()" color="primary" sm>
                <x-ts-icon icon="plus" class="size-4 mr-1" />
                Dodaj miejscowość
            </x-ts-button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center">
                <div class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $totalPeople }}</div>
                <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('person.people') }}</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $peopleWithBirthplace }}</div>
                <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('app.birthplaces_with_location') }}</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ count($birthplaces) }}</div>
                <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('app.unique_places') }}</div>
            </div>
        </div>
    </div>

    {{-- Mapa --}}
    <div class="mb-6 bg-white dark:bg-neutral-700 rounded-lg shadow overflow-hidden relative">
        <div id="birthplaces-map" style="width: 100%; height: 720px;"></div>
        
        {{-- Wskaźnik ładowania --}}
        <div id="loading-indicator" class="absolute inset-0 flex flex-col items-center justify-center bg-white dark:bg-neutral-700 bg-opacity-90 dark:bg-opacity-90 z-10">
            <div class="text-center">
                <svg class="animate-spin h-12 w-12 mx-auto mb-4 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mb-2">
                    Ładowanie mapy...
                </div>
                <div id="loading-progress" class="text-sm text-neutral-600 dark:text-neutral-400">
                    Geokodowanie miejsc urodzenia...
                </div>
            </div>
        </div>
    </div>

    {{-- Zarządzanie miejscowościami --}}
    <div class="bg-white dark:bg-neutral-700 rounded-lg shadow p-4 mb-6">
        <h3 class="text-xl font-bold mb-4 text-neutral-800 dark:text-neutral-200">
            Zarządzanie miejscowościami
        </h3>
        
        @if($places->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-600">
                    <thead class="bg-neutral-50 dark:bg-neutral-600">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">
                                Nazwa
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">
                                Kod pocztowy
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">
                                Liczba osób
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">
                                Współrzędne
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">
                                Akcje
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-neutral-700 divide-y divide-neutral-200 dark:divide-neutral-600">
                        @foreach($places as $place)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ $place->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ $place->postal_code ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ $place->people_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-400">
                                    @if($place->latitude && $place->longitude)
                                        {{ number_format($place->latitude, 4) }}, {{ number_format($place->longitude, 4) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <x-ts-button wire:click="openPlaceModal({{ $place->id }})" color="primary" sm>
                                        <x-ts-icon icon="pencil" class="size-4" />
                                    </x-ts-button>
                                    <x-ts-button wire:click="confirmDeletePlace({{ $place->id }})" color="red" sm>
                                        <x-ts-icon icon="trash" class="size-4" />
                                    </x-ts-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-neutral-600 dark:text-neutral-400">
                Brak miejscowości w bazie danych.
            </div>
        @endif
    </div>

    {{-- Lista miejsc --}}
    <div class="bg-white dark:bg-neutral-700 rounded-lg shadow p-4">
        <h3 class="text-xl font-bold mb-4 text-neutral-800 dark:text-neutral-200">
            {{ __('app.birthplaces_list') }}
        </h3>
        
        @if(count($birthplaces) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($birthplaces as $birthplace)
                    <div class="bg-neutral-50 dark:bg-neutral-600 rounded-lg p-4 border border-neutral-200 dark:border-neutral-500">
                        <div class="font-bold text-lg mb-2 text-neutral-800 dark:text-neutral-200">
                            {{ $birthplace['place'] }}
                            <span class="text-sm font-normal text-neutral-600 dark:text-neutral-400">
                                ({{ $birthplace['count'] }})
                            </span>
                        </div>
                        <div class="space-y-1">
                            @foreach($birthplace['people'] as $person)
                                <div class="text-sm">
                                    <a href="{{ route('people.show', $person['id']) }}" 
                                       class="text-primary-600 dark:text-primary-400 hover:underline">
                                        {{ $person['name'] }}
                                    </a>
                                    @if($person['birth'])
                                        <span class="text-neutral-600 dark:text-neutral-400">
                                            ({{ $person['birth'] }})
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-neutral-600 dark:text-neutral-400">
                {{ __('app.no_birthplaces_found') }}
            </div>
        @endif
    </div>

    {{-- Modal edycji miejscowości --}}
    <x-ts-modal wire="showPlaceModal" title="{{ $editingPlaceId ? 'Edytuj miejscowość' : 'Dodaj miejscowość' }}" size="lg">
        <x-ts-errors class="mb-4" close />
        
        <div class="space-y-4">
            <x-ts-input wire:model="placeName" label="Nazwa miejscowości *" placeholder="np. Warszawa" />
            
            <x-ts-input wire:model="placePostalCode" label="Kod pocztowy" placeholder="np. 00-001" />
            
            <div class="grid grid-cols-2 gap-4">
                <x-ts-input wire:model="placeLatitude" label="Szerokość geograficzna" type="number" step="0.0000001" placeholder="np. 52.2297" />
                
                <x-ts-input wire:model="placeLongitude" label="Długość geograficzna" type="number" step="0.0000001" placeholder="np. 21.0122" />
            </div>
            
            <div class="text-sm text-neutral-600 dark:text-neutral-400">
                <p class="mb-2">💡 Wskazówka: Współrzędne geograficzne są opcjonalne. Jeśli je podasz, mapa będzie używała ich zamiast geokodowania.</p>
                <p>Możesz znaleźć współrzędne na <a href="https://www.google.com/maps" target="_blank" class="text-primary-600 hover:underline">Google Maps</a></p>
            </div>
        </div>

        <x-slot:footer>
            <x-ts-button wire:click="closePlaceModal" color="secondary">
                {{ __('app.cancel') }}
            </x-ts-button>
            <x-ts-button wire:click="savePlace" color="primary">
                {{ __('app.save') }}
            </x-ts-button>
        </x-slot:footer>
    </x-ts-modal>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicjalizacja mapy - centrum na Polsce
    const map = L.map('birthplaces-map').setView([52.0, 19.0], 6);

    // Dodaj warstwę OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    // Grupa markerów dla klastrowania
    const markers = L.markerClusterGroup({
        maxClusterRadius: 50,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true
    });

    // Dane miejsc urodzenia
    const birthplaces = @json($birthplaces);

    // Funkcja do geokodowania miejsca (użycie Nominatim API)
    async function geocodePlace(placeName, postalCode = null) {
        try {
            // Jeśli mamy kod pocztowy, użyj go w pierwszej kolejności
            if (postalCode) {
                let searchQuery = `${placeName}, ${postalCode}`;
                let response = await fetch(
                    `https://nominatim.openstreetmap.org/search?` + 
                    `format=json&` +
                    `q=${encodeURIComponent(searchQuery)}&` +
                    `countrycodes=pl&` +
                    `limit=5&` +
                    `addressdetails=1`
                );
                let data = await response.json();
                
                if (data.length > 0) {
                    // Wybierz najlepszy wynik (najwyższy importance)
                    data.sort((a, b) => (b.importance || 0) - (a.importance || 0));
                    
                    return {
                        lat: parseFloat(data[0].lat),
                        lng: parseFloat(data[0].lon),
                        displayName: data[0].display_name
                    };
                }
            }
            
            // Spróbuj z samą nazwą miejsca (wraz z Polską)
            let response = await fetch(
                `https://nominatim.openstreetmap.org/search?` + 
                `format=json&` +
                `q=${encodeURIComponent(placeName)}&` +
                `countrycodes=pl&` +
                `limit=5&` +
                `addressdetails=1`
            );
            let data = await response.json();
            
            if (data.length > 0) {
                // Wybierz najlepszy wynik (najwyższy importance)
                data.sort((a, b) => (b.importance || 0) - (a.importance || 0));
                
                return {
                    lat: parseFloat(data[0].lat),
                    lng: parseFloat(data[0].lon),
                    displayName: data[0].display_name
                };
            }
            
            // Jeśli nie znaleziono, spróbuj z dodatkowym "Polska"
            response = await fetch(
                `https://nominatim.openstreetmap.org/search?` + 
                `format=json&` +
                `q=${encodeURIComponent(placeName + ', Polska')}&` +
                `limit=3`
            );
            data = await response.json();
            
            if (data.length > 0) {
                return {
                    lat: parseFloat(data[0].lat),
                    lng: parseFloat(data[0].lon),
                    displayName: data[0].display_name
                };
            }
            
            return null;
        } catch (error) {
            console.error('Błąd geokodowania dla:', placeName, error);
            return null;
        }
    }

    // Dodaj markery dla każdego miejsca
    async function addMarkers() {
        const loadingIndicator = document.getElementById('loading-indicator');
        const loadingProgress = document.getElementById('loading-progress');
        const progressBar = document.getElementById('geocoding-progress-bar');
        const progressFill = document.getElementById('geocoding-progress-fill');
        const progressStatus = document.getElementById('geocoding-status');
        const progressPercentage = document.getElementById('geocoding-percentage');
        const progressCount = document.getElementById('geocoding-count');
        
        let successCount = 0;
        let failedPlaces = [];
        
        // Pokaż pasek postępu
        if (birthplaces.length > 0) {
            progressBar.classList.remove('hidden');
            progressCount.textContent = `0 / ${birthplaces.length}`;
        }
        
        for (let i = 0; i < birthplaces.length; i++) {
            const place = birthplaces[i];
            const currentNum = i + 1;
            const percentage = Math.round((currentNum / birthplaces.length) * 100);
            
            // Aktualizuj wskaźnik postępu na pasku górnym
            progressFill.style.width = `${percentage}%`;
            progressPercentage.textContent = `${percentage}%`;
            progressCount.textContent = `${currentNum} / ${birthplaces.length}`;
            progressStatus.textContent = `Przetwarzanie: ${place.place}`;
            
            // Aktualizuj wskaźnik postępu na mapie (stary)
            loadingProgress.textContent = `Geokodowanie ${currentNum} z ${birthplaces.length}: ${place.place}`;
            
            let coords = null;
            
            // Jeśli mamy zapisane współrzędne, użyj ich
            if (place.latitude && place.longitude) {
                coords = {
                    lat: place.latitude,
                    lng: place.longitude,
                    displayName: place.postal_code ? `${place.place}, ${place.postal_code}` : place.place
                };
                console.log(`✓ Użyto zapisanych współrzędnych dla: ${place.place}`);
                successCount++;
            } else {
                // W przeciwnym razie geokoduj
                progressStatus.textContent = `🌍 Geokodowanie: ${place.place}${place.postal_code ? ' (' + place.postal_code + ')' : ''}`;
                await new Promise(resolve => setTimeout(resolve, 1100));
                const searchInfo = place.postal_code ? `${place.place} (${place.postal_code})` : place.place;
                console.log(`Geokodowanie: ${searchInfo}...`);
                coords = await geocodePlace(place.place, place.postal_code);
                
                if (coords) {
                    successCount++;
                    console.log(`✓ Znaleziono: ${coords.displayName || place.place}`);
                }
            }
            
            if (coords) {
                // Stwórz popup z listą osób
                let popupContent = `<div class="font-bold text-lg mb-2">${place.place}</div>`;
                
                // Dodaj informację o kodzie pocztowym jeśli istnieje
                if (place.postal_code) {
                    popupContent += `<div class="text-xs text-neutral-500 mb-1">📮 ${place.postal_code}</div>`;
                }
                
                // Dodaj informację o znalezionej lokalizacji jeśli jest inna niż wprowadzona
                if (coords.displayName && !coords.displayName.includes(place.place) && !place.latitude) {
                    popupContent += `<div class="text-xs text-neutral-500 mb-1 italic">📍 ${coords.displayName}</div>`;
                }
                
                popupContent += `<div class="text-sm text-neutral-600 mb-2">${place.count} ${place.count === 1 ? 'osoba' : 'osób'}</div>`;
                popupContent += '<div class="space-y-1 max-h-48 overflow-y-auto">';
                
                place.people.forEach(person => {
                    popupContent += `<div class="text-sm">
                        <a href="/people/${person.id}" class="text-blue-600 hover:underline">
                            ${person.name}
                        </a>`;
                    if (person.birth) {
                        popupContent += ` <span class="text-neutral-500">(${person.birth})</span>`;
                    }
                    popupContent += '</div>';
                });
                
                popupContent += '</div>';

                // Dodaj marker
                const marker = L.marker([coords.lat, coords.lng])
                    .bindPopup(popupContent, { maxWidth: 300 });
                
                markers.addLayer(marker);
            } else {
                failedPlaces.push(place.place);
                console.warn(`✗ Nie znaleziono lokalizacji dla: ${place.place}`);
            }
        }
        
        map.addLayer(markers);
        
        // Dopasuj widok do wszystkich markerów
        if (markers.getLayers().length > 0) {
            map.fitBounds(markers.getBounds(), { padding: [50, 50] });
        }
        
        // Ukryj wskaźnik ładowania
        loadingIndicator.style.display = 'none';
        
        // Zaktualizuj pasek postępu na "Gotowe"
        progressStatus.textContent = `✅ Gotowe! Załadowano ${successCount} z ${birthplaces.length} miejsc`;
        progressFill.style.width = '100%';
        progressPercentage.textContent = '100%';
        
        // Ukryj pasek postępu po 3 sekundach
        setTimeout(() => {
            progressBar.classList.add('hidden');
        }, 3000);
        
        // Pokaż podsumowanie
        console.log(`\n📊 Podsumowanie geokodowania:`);
        console.log(`   Sukces: ${successCount}/${birthplaces.length}`);
        if (failedPlaces.length > 0) {
            console.log(`   Nie znaleziono lokalizacji dla:`);
            failedPlaces.forEach(place => console.log(`   - ${place}`));
            console.log(`\n💡 Wskazówka: Dodaj kod pocztowy lub więcej szczegółów do miejsca urodzenia:`);
            console.log(`   - Edytuj miejscowość i dodaj kod pocztowy (np. 19-400)`);
            console.log(`   - Dodaj współrzędne geograficzne ręcznie`);
            console.log(`   - Lub użyj pełniejszej nazwy (np. "Śliwno, powiat olecki, warmińsko-mazurskie")`);
        }
    }

    // Rozpocznij dodawanie markerów
    if (birthplaces.length > 0) {
        addMarkers();
    } else {
        // Jeśli nie ma miejsc, ukryj wskaźnik od razu
        document.getElementById('loading-indicator').style.display = 'none';
    }
});
</script>
@endpush


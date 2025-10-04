<?php

declare(strict_types=1);

namespace App\Livewire\People;

use App\Models\Person;
use App\Models\Place;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

final class BirthplacesMap extends Component
{
    use Interactions;

    public array $birthplaces = [];

    public int $totalPeople = 0;

    public int $peopleWithBirthplace = 0;

    public bool $showPlaceModal = false;

    public ?int $editingPlaceId = null;

    public string $placeName = '';

    public string $placePostalCode = '';

    public ?float $placeLatitude = null;

    public ?float $placeLongitude = null;

    // ------------------------------------------------------------------------------
    public function mount(): void
    {
        $this->loadBirthplaces();
    }

    // ------------------------------------------------------------------------------
    public function render(): View
    {
        return view('livewire.people.birthplaces-map');
    }

    // ------------------------------------------------------------------------------
    public function openPlaceModal(?int $placeId = null): void
    {
        $this->showPlaceModal = true;
        $this->editingPlaceId = $placeId;

        if ($placeId) {
            $place = Place::forTeam(auth()->user()->currentTeam->id)->find($placeId);
            if ($place) {
                $this->placeName = $place->name;
                $this->placePostalCode = $place->postal_code ?? '';
                $this->placeLatitude = $place->latitude;
                $this->placeLongitude = $place->longitude;
            }
        } else {
            $this->resetPlaceForm();
        }
    }

    public function closePlaceModal(): void
    {
        $this->showPlaceModal = false;
        $this->resetPlaceForm();
    }

    public function savePlace(): void
    {
        $validated = $this->validate([
            'placeName' => 'required|string|max:255',
            'placePostalCode' => 'nullable|string|max:20',
            'placeLatitude' => 'nullable|numeric|between:-90,90',
            'placeLongitude' => 'nullable|numeric|between:-180,180',
        ], [], [
            'placeName' => __('app.name'),
            'placePostalCode' => __('person.postal_code'),
            'placeLatitude' => 'Szerokość geograficzna',
            'placeLongitude' => 'Długość geograficzna',
        ]);

        if ($this->editingPlaceId) {
            $place = Place::forTeam(auth()->user()->currentTeam->id)
                ->findOrFail($this->editingPlaceId);
            $place->update([
                'name' => $this->placeName,
                'postal_code' => $this->placePostalCode ?: null,
                'latitude' => $this->placeLatitude,
                'longitude' => $this->placeLongitude,
            ]);
            $this->toast()->success(__('app.save'), 'Miejsce zostało zaktualizowane.')->send();
        } else {
            Place::create([
                'team_id' => auth()->user()->currentTeam->id,
                'name' => $this->placeName,
                'postal_code' => $this->placePostalCode ?: null,
                'latitude' => $this->placeLatitude,
                'longitude' => $this->placeLongitude,
            ]);
            $this->toast()->success(__('app.save'), 'Miejsce zostało dodane.')->send();
        }

        $this->closePlaceModal();
        $this->loadBirthplaces();
    }

    public function confirmDeletePlace(int $placeId): void
    {
        $place = Place::forTeam(auth()->user()->currentTeam->id)->find($placeId);

        $this->dialog()
            ->question(__('app.attention') . '!', 'Czy na pewno chcesz usunąć to miejsce?')
            ->confirm(__('app.delete_yes'))
            ->cancel(__('app.cancel'))
            ->hook([
                'ok' => [
                    'method' => 'deletePlace',
                    'params' => $placeId,
                ],
            ])
            ->send();
    }

    public function deletePlace(int $placeId): void
    {
        $place = Place::forTeam(auth()->user()->currentTeam->id)->find($placeId);

        if ($place) {
            // Ustaw birthplace_id na null dla wszystkich osób związanych z tym miejscem
            Person::where('team_id', auth()->user()->currentTeam->id)
                ->where('birthplace_id', $placeId)
                ->update(['birthplace_id' => null]);

            $place->delete();
            $this->toast()->success(__('app.delete'), 'Miejsce zostało usunięte.')->send();
            $this->loadBirthplaces();
        }
    }

    // ------------------------------------------------------------------------------
    private function loadBirthplaces(): void
    {
        $teamId = auth()->user()->currentTeam->id;
        
        // Pobierz wszystkie miejsca urodzenia z liczbą osób z nowej struktury dla obecnego teamu
        $places = Place::forTeam($teamId)
            ->withCount('people')
            ->having('people_count', '>', 0)
            ->get();

        $this->totalPeople = Person::where('team_id', $teamId)->count();
        $this->peopleWithBirthplace = Person::where('team_id', $teamId)
            ->whereNotNull('birthplace_id')
            ->count();

        // Przygotuj dane dla mapy
        $this->birthplaces = $places->map(function ($place) use ($teamId) {
            $people = $place->people()
                ->where('team_id', $teamId)
                ->select('id', 'firstname', 'surname', 'dob', 'yob')
                ->orderBy('surname')
                ->orderBy('firstname')
                ->get();

            return [
                'place_id' => $place->id,
                'place' => $place->name,
                'postal_code' => $place->postal_code,
                'count' => $people->count(),
                'latitude' => $place->latitude,
                'longitude' => $place->longitude,
                'people' => $people->map(fn ($person) => [
                    'id' => $person->id,
                    'name' => $person->name,
                    'birth' => $person->birth_formatted,
                ])->toArray(),
            ];
        })->toArray();
    }

    private function resetPlaceForm(): void
    {
        $this->editingPlaceId = null;
        $this->placeName = '';
        $this->placePostalCode = '';
        $this->placeLatitude = null;
        $this->placeLongitude = null;
        $this->resetValidation();
    }
}


<div wire:key="profile-{{ $person->id }}" class="min-w-sm max-w-3xl flex flex-col rounded-sm bg-white shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] dark:bg-neutral-700 text-neutral-800 dark:text-neutral-50">
    <div class="flex flex-col p-2 text-lg font-medium border-b-2 rounded-t h-14 min-h-min border-neutral-100 dark:border-neutral-600 dark:text-neutral-50">
        <div class="flex flex-wrap items-start justify-center gap-2">
            <div class="items-center justify-center flex-1 grow max-w-full align-middle min-w-max">
                {{ $editMode ? __('person.edit_profile') : __('person.profile') }}
            </div>

            <div class="flex gap-2 items-center">
                @if ($editMode)
                    {{-- Przyciski w trybie edycji --}}
                    <x-ts-button wire:click="saveProfile" color="primary" class="text-xs py-1">
                        <x-ts-icon icon="tabler.device-floppy" class="inline-block size-4" />
                        <span class="hidden sm:inline ml-1">{{ __('app.save') }}</span>
                    </x-ts-button>
                    <x-ts-button wire:click="cancelEdit" color="secondary" class="text-xs py-1">
                        <x-ts-icon icon="tabler.x" class="inline-block size-4" />
                        <span class="hidden sm:inline ml-1">{{ __('app.cancel') }}</span>
                    </x-ts-button>
                @else
                    {{-- Przyciski w trybie wyświetlania --}}
                    @if (auth()->user()->hasPermission('person:update'))
                        <x-ts-button wire:click="enableEditMode" color="primary" class="text-xs py-1">
                            <x-ts-icon icon="tabler.pencil" class="inline-block size-4" />
                            <span class="hidden sm:inline ml-1">{{ __('app.edit') }}</span>
                        </x-ts-button>
                    @endif

                    @if (auth()->user()->hasPermission('person:update') or auth()->user()->hasPermission('person:delete'))
                        <x-ts-dropdown icon="tabler.dots-vertical" position="bottom-end">
                            @if (auth()->user()->hasPermission('person:update'))
                                <a href="/people/{{ $person->id }}/edit-contact">
                                    <x-ts-dropdown.items>
                                        <x-ts-icon icon="tabler.address-book" class="inline-block size-5 mr-2" />
                                        {{ __('person.edit_contact') }}
                                    </x-ts-dropdown.items>
                                </a>

                                <hr />
                                <a href="/people/{{ $person->id }}/edit-photos">
                                    <x-ts-dropdown.items>
                                        <x-ts-icon icon="tabler.photo" class="inline-block size-5 mr-2" />
                                        {{ __('person.edit_photos') }}
                                    </x-ts-dropdown.items>
                                </a>
                            @endif

                            @if (auth()->user()->hasPermission('person:delete') and $person->isDeletable())
                                <hr />

                                <x-ts-dropdown.items separator class="text-red-600! dark:text-red-400!" wire:click="confirm()">
                                    <x-ts-icon icon="tabler.trash" class="inline-block size-5 mr-2" />
                                    {{ __('person.delete_person') }}
                                </x-ts-dropdown.items>
                            @endif
                        </x-ts-dropdown>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- image --}}
    <div class="grid justify-center pt-2">
        <livewire:people.gallery :person="$person" class="max-w-sm" />
    </div>

    {{-- lifetime & age --}}
    <div class="flex px-2">
        <div class="grow">
            {!! isset($person->lifetime) ? $person->lifetime : '&nbsp;' !!}
        </div>

        <div class="grow text-end">
            {!! isset($person->age) ? $person->age . ' ' . trans_choice('person.years', $person->age) : '&nbsp;' !!}
        </div>
    </div>

    {{-- data --}}
    @if ($editMode)
        {{-- Edit Mode --}}
        <form wire:submit="saveProfile" class="p-4 bg-neutral-50 dark:bg-neutral-800">
            <x-ts-errors class="mb-4" close />

            <div class="grid grid-cols-6 gap-4">
                {{-- firstname --}}
                <div class="col-span-6 md:col-span-3">
                    <x-ts-input wire:model="firstname" id="firstname" label="{{ __('person.firstname') }} :" />
                </div>

                {{-- surname --}}
                <div class="col-span-6 md:col-span-3">
                    <x-ts-input wire:model="surname" id="surname" label="{{ __('person.surname') }} : *" required />
                </div>

                {{-- birthname --}}
                <div class="col-span-6 md:col-span-3">
                    <x-ts-input wire:model="birthname" id="birthname" label="{{ __('person.birthname') }} :" />
                </div>
                <x-hr.narrow class="col-span-6 my-0!" />

                {{-- sex --}}
                <div class="col-span-6">
                    <x-label for="sex" class="mr-5" value="{{ __('person.sex') }}: *" />
                    <div class="flex gap-4 mt-2">
                        <x-ts-radio color="primary" wire:model="sex" name="sex" id="sexM" value="m" label="{{ __('app.male') }}" />
                        <x-ts-radio color="primary" wire:model="sex" name="sex" id="sexF" value="f" label="{{ __('app.female') }}" />
                    </div>
                </div>

                <x-hr.narrow class="col-span-6 my-0!" />

                {{-- yob --}}
                <div class="col-span-6 md:col-span-3">
                    <x-ts-input wire:model="yob" id="yob" label="{{ __('person.yob') }} :" type="number" max="{{ date('Y') }}" />
                </div>

                {{-- dob --}}
                <div class="col-span-6 md:col-span-3">
                    <x-ts-input wire:model="dob" id="dob" label="{{ __('person.dob') }} :" 
                        placeholder="RRRR-MM-DD" 
                        x-mask="9999-99-99"
                        hint="Format: RRRR-MM-DD (np. 1950-12-25)" />
                </div>

                {{-- birthplace_id --}}
                <div class="col-span-6">
                    <x-ts-select.styled 
                        wire:model="birthplace_id" 
                        id="birthplace_id" 
                        label="{{ __('person.pob') }} :" 
                        :options="$this->places()" 
                        select="label:full_name|value:id" 
                        placeholder="{{ __('app.select') }} ..." 
                        searchable />
                </div>

                <x-hr.narrow class="col-span-6 my-0!" />

                {{-- summary --}}
                <div class="col-span-6">
                    <x-ts-textarea wire:model="summary" id="summary" label="{{ __('person.summary') }} :" maxlength="65535" count />
                </div>

                <x-hr.narrow class="col-span-6 my-0!" />

                {{-- Death section header --}}
                <div class="col-span-6">
                    <h4 class="text-lg font-medium text-neutral-800 dark:text-neutral-200">
                        <x-ts-icon icon="tabler.grave-2" class="inline-block size-5 mr-2" />
                        {{ __('person.death') }}
                    </h4>
                </div>

                {{-- yod --}}
                <div class="col-span-6 md:col-span-3">
                    <x-ts-input wire:model="yod" id="yod" label="{{ __('person.yod') }} :" type="number" max="{{ date('Y') }}" />
                </div>

                {{-- dod --}}
                <div class="col-span-6 md:col-span-3">
                    <x-ts-input wire:model="dod" id="dod" label="{{ __('person.dod') }} :" 
                        placeholder="RRRR-MM-DD" 
                        x-mask="9999-99-99"
                        hint="Format: RRRR-MM-DD (np. 2020-06-15)" />
                </div>

                {{-- pod --}}
                <div class="col-span-6">
                    <x-ts-input wire:model="pod" id="pod" label="{{ __('person.pod') }} :" />
                </div>

                <x-hr.narrow class="col-span-6 my-0!" />

                {{-- Cemetery location header --}}
                <div class="col-span-5">
                    <h4 class="text-lg font-medium text-neutral-800 dark:text-neutral-200">{{ __('person.cemetery_location') }}</h4>
                </div>

                {{-- show on google maps button --}}
                <div class="col-span-1 text-end">
                    @if ($person->cemetery_google)
                        <a target="_blank" href="{{ $person->cemetery_google }}">
                            <x-ts-button color="cyan" class="p-2! mb-2 text-white" title="{{ __('app.show_on_google_maps') }}">
                                <x-ts-icon icon="tabler.brand-google-maps" class="inline-block size-5" />
                            </x-ts-button>
                        </a>
                    @endif
                </div>

                {{-- cemetery_location_name --}}
                <div class="col-span-6">
                    <x-ts-input wire:model="cemetery_location_name" id="cemetery_location_name" label="{{ __('metadata.location_name') }} :" />
                </div>

                {{-- cemetery_location_address --}}
                <div class="col-span-6">
                    <x-ts-textarea wire:model="cemetery_location_address" id="cemetery_location_address" label="{{ __('metadata.address') }} :" resize-auto />
                </div>

                {{-- cemetery_location_latitude --}}
                <div class="col-span-6 md:col-span-3">
                    <x-ts-input wire:model="cemetery_location_latitude" id="cemetery_location_latitude" label="{{ __('metadata.latitude') }} :" />
                </div>

                {{-- cemetery_location_longitude --}}
                <div class="col-span-6 md:col-span-3">
                    <x-ts-input wire:model="cemetery_location_longitude" id="cemetery_location_longitude" label="{{ __('metadata.longitude') }} :" />
                </div>
            </div>
        </form>
    @else
        {{-- View Mode --}}
        <div class="p-2">
            <table class="w-full">
                <tbody>
                    <tr class="align-top">
                        <td class="pr-2 border-t-2 border-r-2">{{ __('person.firstname') }}</td>
                        <td class="pl-2 break-words border-t-2 max-w-sm">{{ $person->firstname }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="pr-2 border-r-2">{{ __('person.surname') }}</td>
                        <td class="pl-2 break-words max-w-sm">{{ $person->surname }}</td>
                    </tr>
                    <tr class="align-top">
                        <td class="pr-2 border-r-2">{{ __('person.birthname') }}</td>
                        <td class="pl-2 break-words max-w-sm">{{ $person->birthname }}</td>
                    </tr>

                    <tr class="align-top">
                        <td class="pr-2 border-b-2  border-t-2 border-r-2">{{ __('person.sex') }}</td>
                        <td class="pl-2 border-b-2 border-t-2">
                            {{ $person->sex === 'm' ? __('app.male') : __('app.female') }}
                            <x-ts-icon icon="tabler.{{ $person->sex === 'm' ? 'gender-male' : 'gender-female' }}" class="inline-block size-5" />
                        </td>
                    </tr>

                    <tr class="align-top">
                        <td class="pr-2 border-r-2">{{ __('person.dob') }}</td>
                        <td class="pl-2">
                            {{ $person->birth_formatted }}
                            @if ($person->isBirthdayToday())
                                <x-ts-icon icon="tabler.cake" class="inline-block size-5 text-red-600 dark:text-red-400" />
                            @endif
                        </td>
                    </tr>
                    <tr class="align-top border-b-2">
                        <td class="pr-2 border-r-2">{{ __('person.pob') }}</td>
                        <td class="pl-2 break-words max-w-sm">{{ $person->birthplace_formatted }}</td>
                    </tr>

                    @if ($person->isDeceased())
                        <tr class="align-top">
                            <td class="pr-2 border-r-2">{{ __('person.dod') }}</td>
                            <td class="pl-2">
                                {{ $person->death_formatted }}
                                @if ($person->isDeathdayToday())
                                    <x-ts-icon icon="tabler.cake" class="inline-block size-5 text-red-600 dark:text-red-400" />
                                @endif
                            </td>
                        </tr>
                        <tr class="align-top border-b-2">
                            <td class="pr-2 border-r-2">{{ __('person.pod') }}</td>
                            <td class="pl-2 break-words max-w-sm">{{ $person->pod }}</td>
                        </tr>
                        <tr class="align-top">
                            <td class="pr-2 border-r-2">{{ __('person.cemetery') }}</td>
                            <td class="pl-2 break-words max-w-sm">{{ $person->getMetadataValue('cemetery_location_name') }}</td>
                        </tr>
                        <tr class="align-top">
                            <td class="pr-2 border-b-2 border-r-2">
                                @if ($person->cemetery_google)
                                    <a target="_blank" href="{{ $person->cemetery_google }}">
                                        <x-ts-button color="cyan" class="p-2! mb-2 text-white" title="{{ __('app.show_on_google_maps') }}">
                                            <x-ts-icon icon="tabler.brand-google-maps" class="inline-block size-5" />
                                        </x-ts-button>
                                    </a>
                                @endif
                            </td>
                            <td class="pl-2 break-words whitespace-pre-line border-b-2 max-w-sm">{{ $person->getMetadataValue('cemetery_location_address') }}</td>
                        </tr>
                    @else
                        <tr class="align-top">
                            <td class="pr-2 border-b-2 border-r-2">
                                {{ __('person.address') }}<br />
                                @if ($person->address)
                                    <a target="_blank" href="{{ $person->address_google }}">
                                        <x-ts-button color="cyan" class="p-2! mb-2 text-white" title="{{ __('app.show_on_google_maps') }}">
                                            <x-ts-icon icon="tabler.brand-google-maps" class="inline-block size-5" />
                                        </x-ts-button>
                                    </a>
                                @endif
                            </td>
                            <td class="pl-2 break-words whitespace-pre-line border-b-2 max-w-sm">{{ $person->address }}</td>
                        </tr>
                        <tr class="align-top">
                            <td class="pr-2 border-b-2 border-r-2">{{ __('person.phone') }}</td>
                            <td class="pl-2 break-words border-b-2 max-w-sm">{{ $person->phone }}</td>
                        </tr>
                    @endif

                    <tr class="align-top border-b-2">
                        <td class="pr-2 border-r-2">{{ __('person.summary') }}</td>
                        <td class="pl-2 break-words whitespace-pre-line max-w-sm">{{ $person->summary }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</div>

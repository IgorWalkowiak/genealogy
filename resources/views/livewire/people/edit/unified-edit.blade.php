<div>
    <div class="md:w-3xl flex flex-col rounded-sm bg-white shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] dark:bg-neutral-700 text-neutral-800 dark:text-neutral-50">
        <div class="flex flex-col p-2 text-lg font-medium border-b-2 rounded-t h-14 min-h-min border-neutral-100 dark:border-neutral-600 dark:text-neutral-50">
            <div class="flex flex-wrap items-start justify-center gap-2">
                <div class="flex-1 grow max-w-full min-w-max">
                    {{ __('person.edit') }} - {{ $person->name }}
                </div>
            </div>
        </div>

        <div class="p-4 bg-neutral-200">
            <x-ts-tab selected="profile">
                {{-- Profile Tab --}}
                <x-ts-tab.items tab="profile" label="{{ __('person.edit_profile') }}" icon="tabler.id">
                    <form wire:submit="saveProfile" class="mt-4">
                        <x-ts-errors class="mb-2" close />

                        <div class="grid grid-cols-6 gap-5">
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

                            {{-- nickname --}}
                            <div class="col-span-6 md:col-span-3">
                                <x-ts-input wire:model="nickname" id="nickname" label="{{ __('person.nickname') }} :" />
                            </div>
                            <x-hr.narrow class="col-span-6 my-0!" />

                            {{-- sex --}}
                            <div class="col-span-3">
                                <x-label for="sex" class="mr-5" value="{{ __('person.sex') }} ({{ __('person.biological') }}) : *" />
                                <div class="flex">
                                    <div class="mt-3 mb-[0.125rem] mr-4 inline-block min-h-[1.5rem] pl-[1.5rem]">
                                        <x-ts-radio color="primary" wire:model="sex" name="sex" id="sexM" value="m" label="{{ __('app.male') }}" />
                                    </div>
                                    <div class="mt-3 mb-[0.125rem] mr-4 inline-block min-h-[1.5rem] pl-[1.5rem]">
                                        <x-ts-radio color="primary" wire:model="sex" name="sex" id="sexF" value="f" label="{{ __('app.female') }}" />
                                    </div>
                                </div>
                            </div>
                            <x-hr.narrow class="col-span-6 my-0!" />

                            {{-- yob --}}
                            <div class="col-span-6 md:col-span-3">
                                <x-ts-input wire:model="yob" id="yob" label="{{ __('person.yob') }} :" autocomplete="yob" type="number" max="{{ date('Y') }}" />
                            </div>

                            {{-- dob --}}
                            <div class="col-span-6 md:col-span-3">
                                <x-ts-date wire:model="dob" id="dob" name="dob" label="{{ __('person.dob') }} :" format="YYYY-MM-DD"
                                    :max-date="now()" placeholder="{{ __('app.select') }} ..." />
                            </div>

                            {{-- pob --}}
                            <div class="col-span-6">
                                <x-ts-input wire:model="pob" id="pob" label="{{ __('person.pob') }} :" autocomplete="pob" />
                            </div>
                            <x-hr.narrow class="col-span-6 my-0!" />

                            {{-- summary --}}
                            <div class="col-span-6">
                                <x-ts-textarea wire:model="summary" id="summary" label="{{ __('person.summary') }} :" autocomplete="summary"
                                    maxlength="65535" count />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-ts-button type="submit" color="primary">
                                {{ __('app.save') }}
                            </x-ts-button>
                        </div>
                    </form>
                </x-ts-tab.items>

                {{-- Contact Tab --}}
                <x-ts-tab.items tab="contact" label="{{ __('person.edit_contact') }}" icon="tabler.address-book">
                    <form wire:submit="saveContact" class="mt-4">
                        <x-ts-errors class="mb-2" close />

                        <div class="grid grid-cols-6 gap-5">
                            {{-- street --}}
                            <div class="col-span-4">
                                <x-ts-input wire:model="street" id="street" label="{{ __('person.street') }} :" autocomplete="street" />
                            </div>

                            {{-- number --}}
                            <div class="col-span-2">
                                <x-ts-input wire:model="number" id="number" label="{{ __('person.number') }} :" autocomplete="number" />
                            </div>

                            {{-- postal_code --}}
                            <div class="col-span-2">
                                <x-ts-input wire:model="postal_code" id="postal_code" label="{{ __('person.postal_code') }} :" autocomplete="postal_code" />
                            </div>

                            {{-- city --}}
                            <div class="col-span-4">
                                <x-ts-input wire:model="city" id="city" label="{{ __('person.city') }} :" autocomplete="city" />
                            </div>

                            {{-- province --}}
                            <div class="col-span-6 md:col-span-3">
                                <x-ts-input wire:model="province" id="province" label="{{ __('person.province') }} :" autocomplete="province" />
                            </div>

                            {{-- state --}}
                            <div class="col-span-6 md:col-span-3">
                                <x-ts-input wire:model="state" id="state" label="{{ __('person.state') }} :" autocomplete="state" />
                            </div>

                            {{-- country --}}
                            <div class="col-span-5">
                                <x-ts-select.styled wire:model="country" id="country" label="{{ __('person.country') }} :" :options="$this->countries()" select="label:name|value:id"
                                    placeholder="{{ __('app.select') }} ..." searchable />
                            </div>

                            {{-- show on google maps button --}}
                            <div class="h-4 col-span-1 pt-5 text-end">
                                @if ($person->address_google)
                                    <x-ts-button href="{{ $person->address_google }}" target="_blank" color="cyan" class="p-2! text-white" title="{{ __('app.show_on_google_maps') }}">
                                        <x-ts-icon icon="tabler.brand-google-maps" class="inline-block size-5" />
                                    </x-ts-button>
                                @endif
                            </div>
                            <x-hr.narrow class="col-span-6 my-0!" />

                            {{-- phone --}}
                            <div class="col-span-6">
                                <x-ts-input wire:model="phone" id="phone" label="{{ __('person.phone') }} :" autocomplete="phone" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-ts-button type="submit" color="primary">
                                {{ __('app.save') }}
                            </x-ts-button>
                        </div>
                    </form>
                </x-ts-tab.items>

                {{-- Death Tab --}}
                <x-ts-tab.items tab="death" label="{{ __('person.edit_death') }}" icon="tabler.grave-2">
                    <form wire:submit="saveDeath" class="mt-4">
                        <x-ts-errors class="mb-2" close />

                        <div class="grid grid-cols-6 gap-5">
                            {{-- yod --}}
                            <div class="col-span-3">
                                <x-ts-input wire:model="yod" id="yod" label="{{ __('person.yod') }} :" type="number" max="{{ date('Y') }}"/>
                            </div>

                            {{-- dod --}}
                            <div class="col-span-3">
                                <x-ts-date wire:model="dod" id="dod" label="{{ __('person.dod') }} :" format="YYYY-MM-DD" :max-date="now()"
                                    placeholder="{{ __('app.select') }} ..." />
                            </div>

                            {{-- pod --}}
                            <div class="col-span-6">
                                <x-ts-input wire:model="pod" id="pod" label="{{ __('person.pod') }} :" />
                            </div>
                            <x-hr.narrow class="col-span-6 my-0!" />

                            <div class="h-4 col-span-5">
                                <h4 class="text-lg font-medium text-neutral-800">{{ __('person.cemetery_location') }}</h4>
                            </div>

                            {{-- show on google maps button --}}
                            <div class="h-4 col-span-1 text-end">
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
                                <x-ts-textarea wire:model="cemetery_location_address" id="cemetery_location_address" label="{{ __('metadata.address') }} :"
                                    resize-auto />
                            </div>

                            {{-- cemetery_location_latitude --}}
                            <div class="col-span-3">
                                <x-ts-input wire:model="cemetery_location_latitude" id="cemetery_location_latitude" label="{{ __('metadata.latitude') }} :" />
                            </div>

                            {{-- cemetery_location_longitude --}}
                            <div class="col-span-3">
                                <x-ts-input wire:model="cemetery_location_longitude" id="cemetery_location_longitude" label="{{ __('metadata.longitude') }} :" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-ts-button type="submit" color="primary">
                                {{ __('app.save') }}
                            </x-ts-button>
                        </div>
                    </form>
                </x-ts-tab.items>

                {{-- Family Tab --}}
                <x-ts-tab.items tab="family" label="{{ __('person.edit_family') }}" icon="tabler.users">
                    <form wire:submit="saveFamily" class="mt-4">
                        <x-ts-errors class="mb-2" close />

                        <div class="grid grid-cols-6 gap-5">
                            {{-- father_id --}}
                            <div class="col-span-6">
                                <x-ts-select.styled wire:model="father_id" id="father_id" label="{{ __('person.father') }} ({{ __('person.biological') }}) :" :options="$fathers"
                                    select="label:name|value:id" placeholder="{{ __('app.select') }} ..." searchable />
                            </div>

                            {{-- mother_id --}}
                            <div class="col-span-6">
                                <x-ts-select.styled wire:model="mother_id" id="mother_id" label="{{ __('person.mother') }} ({{ __('person.biological') }}) :" :options="$mothers"
                                    select="label:name|value:id" placeholder="{{ __('app.select') }} ..." searchable />
                            </div>

                            <div class="col-span-6">
                                <x-ts-alert color="cyan" icon="tabler.exclamation-circle" close>
                                    <x-slot:title>
                                        {{ __('team.personal_team_caution') }}
                                    </x-slot:title>

                                    <p>{{ __('person.family_caution_1') }}</p>

                                    <x-hr.narrow class="col-span-6" />

                                    <p>{{ __('person.family_caution_2') }}</p>
                                </x-ts-alert>
                            </div>

                            {{-- parents_id --}}
                            <div class="col-span-6">
                                <x-ts-select.styled wire:model="parents_id" id="parents_id" label="{{ __('person.parents') }} :" :options="$parents" select="label:couple|value:id"
                                    placeholder="{{ __('app.select') }} ..." searchable />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-ts-button type="submit" color="primary">
                                {{ __('app.save') }}
                            </x-ts-button>
                        </div>
                    </form>
                </x-ts-tab.items>
            </x-ts-tab>
        </div>
    </div>
</div>


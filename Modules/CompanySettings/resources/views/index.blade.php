<x-app-layout>
    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <p class="text-sm font-semibold text-primary">Administration</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-1">Company Settings</h1>
                <p class="text-sm text-slate-500 mt-1">Configure the information that appears on your invoices.</p>
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <i class="fa-solid fa-shield-halved text-primary"></i>
                <span>Secure business profile</span>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @php
            $isEdit = isset($setting) && $setting;
            $formAction = $isEdit ? route('companysettings.update', $setting) : route('companysettings.store');
        @endphp

        <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <section class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary flex items-center justify-center"><i class="fa-solid fa-building"></i></div>
                        <div><h2 class="font-bold text-slate-800">Business Information</h2><p class="text-xs text-slate-500">Basic company details for invoice headers.</p></div>
                    </div>
                    <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <x-input-label for="company_name" value="Company name" />
                            <x-text-input id="company_name" name="company_name" class="mt-1 block w-full" :value="old('company_name', $setting->company_name ?? '')" required />
                            <x-input-error :messages="$errors->get('company_name')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="legal_name" value="Legal name" />
                            <x-text-input id="legal_name" name="legal_name" class="mt-1 block w-full" :value="old('legal_name', $setting->legal_name ?? '')" />
                            <x-input-error :messages="$errors->get('legal_name')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="phone" value="Phone number" />
                            <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $setting->phone ?? '')" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Business email" />
                            <x-text-input id="email" type="email" name="email" class="mt-1 block w-full" :value="old('email', $setting->email ?? '')" />
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="website" value="Website" />
                            <x-text-input id="website" type="url" name="website" class="mt-1 block w-full" :value="old('website', $setting->website ?? '')" placeholder="https://" />
                            <x-input-error :messages="$errors->get('website')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="tin_number" value="TIN number" />
                            <x-text-input id="tin_number" name="tin_number" class="mt-1 block w-full" :value="old('tin_number', $setting->tin_number ?? '')" />
                            <x-input-error :messages="$errors->get('tin_number')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="bin_number" value="BIN number" />
                            <x-text-input id="bin_number" name="bin_number" class="mt-1 block w-full" :value="old('bin_number', $setting->bin_number ?? '')" />
                            <x-input-error :messages="$errors->get('bin_number')" class="mt-1" />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="address" value="Business address" />
                            <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-primary focus:ring-primary">{{ old('address', $setting->address ?? '') }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-1" />
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden h-fit">
                    <div class="px-5 py-4 border-b border-slate-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary flex items-center justify-center"><i class="fa-solid fa-image"></i></div>
                        <div><h2 class="font-bold text-slate-800">Branding</h2><p class="text-xs text-slate-500">Logo shown on printed bills.</p></div>
                    </div>
                    <div class="p-5">
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-center">
                            @if (!empty($setting?->logo_path))
                                <img src="{{ Storage::disk('public')->url($setting->logo_path) }}" alt="Company logo" class="mx-auto h-24 max-w-full object-contain mb-4">
                            @else
                                <img src="{{ asset('logo.png') }}" alt="Logo preview" class="mx-auto h-24 max-w-full object-contain mb-4">
                            @endif
                            <label for="logo" class="cursor-pointer inline-flex items-center gap-2 rounded-lg bg-white border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:border-primary hover:text-primary">
                                <i class="fa-solid fa-upload"></i> Upload logo
                            </label>
                            <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="hidden">
                            <p class="text-xs text-slate-400 mt-3">PNG, JPG or WEBP. Maximum 2MB.</p>
                        </div>
                        <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                    </div>
                </section>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-slate-200 flex items-center gap-3"><div class="w-10 h-10 rounded-xl bg-primary-soft text-primary flex items-center justify-center"><i class="fa-solid fa-file-invoice"></i></div><div><h2 class="font-bold text-slate-800">Invoice Defaults</h2><p class="text-xs text-slate-500">Default numbering and currency.</p></div></div>
                    <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div><x-input-label for="invoice_prefix" value="Invoice prefix" /><x-text-input id="invoice_prefix" name="invoice_prefix" class="mt-1 block w-full" :value="old('invoice_prefix', $setting->invoice_prefix ?? 'INV')" required /><x-input-error :messages="$errors->get('invoice_prefix')" class="mt-1" /></div>
                        <div><x-input-label for="invoice_next_number" value="Next number" /><x-text-input id="invoice_next_number" type="number" min="1" name="invoice_next_number" class="mt-1 block w-full" :value="old('invoice_next_number', $setting->invoice_next_number ?? 1)" required /><x-input-error :messages="$errors->get('invoice_next_number')" class="mt-1" /></div>
                        <div><x-input-label for="currency_code" value="Currency code" /><x-text-input id="currency_code" name="currency_code" maxlength="3" class="mt-1 block w-full uppercase" :value="old('currency_code', $setting->currency_code ?? 'BDT')" required /><x-input-error :messages="$errors->get('currency_code')" class="mt-1" /></div>
                        <div><x-input-label for="currency_symbol" value="Currency symbol" /><x-text-input id="currency_symbol" name="currency_symbol" class="mt-1 block w-full" :value="old('currency_symbol', $setting->currency_symbol ?? '৳')" required /><x-input-error :messages="$errors->get('currency_symbol')" class="mt-1" /></div>
                    </div>
                </section>

                @php
                    $bankDetails = old('bank_details', $setting->bank_details ?? []);
                    if (isset($bankDetails['bank_name'])) {
                        $bankDetails = [$bankDetails];
                    }
                    $bankDetails = count($bankDetails) ? array_values($bankDetails) : [[]];
                @endphp
                <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden lg:col-span-2">
                    <div class="px-5 sm:px-6 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3"><div class="w-10 h-10 rounded-xl bg-primary-soft text-primary flex items-center justify-center"><i class="fa-solid fa-building-columns"></i></div><div><h2 class="font-bold text-slate-800">Payment Details</h2><p class="text-xs text-slate-500">Add all bank accounts shown on printed invoices.</p></div></div>
                        <button type="button" id="addBankAccount" class="btn-primary inline-flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-bold"><i class="fa-solid fa-plus"></i><span class="hidden sm:inline">Add More</span><span class="sm:hidden">Add</span></button>
                    </div>
                    <div id="bankAccounts" class="p-5 sm:p-6 space-y-4">
                        @foreach ($bankDetails as $index => $bank)
                            <div class="bank-account-row rounded-xl border border-slate-200 bg-slate-50 p-4" data-bank-index="{{ $index }}">
                                <div class="flex items-center justify-between mb-4"><p class="text-sm font-bold text-slate-700">Bank account <span class="bank-account-number">{{ $index + 1 }}</span></p><button type="button" class="remove-bank-account text-xs font-semibold text-red-500 hover:text-red-700 {{ count($bankDetails) === 1 ? 'hidden' : '' }}"><i class="fa-solid fa-trash mr-1"></i>Remove</button></div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div><label class="block text-sm font-medium text-gray-700" for="bank_name_{{ $index }}">Bank name</label><input id="bank_name_{{ $index }}" name="bank_details[{{ $index }}][bank_name]" value="{{ $bank['bank_name'] ?? '' }}" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-primary focus:ring-primary" /></div>
                                    <div><label class="block text-sm font-medium text-gray-700" for="account_name_{{ $index }}">Account name</label><input id="account_name_{{ $index }}" name="bank_details[{{ $index }}][account_name]" value="{{ $bank['account_name'] ?? '' }}" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-primary focus:ring-primary" /></div>
                                    <div><label class="block text-sm font-medium text-gray-700" for="account_number_{{ $index }}">Account number</label><input id="account_number_{{ $index }}" name="bank_details[{{ $index }}][account_number]" value="{{ $bank['account_number'] ?? '' }}" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-primary focus:ring-primary" /></div>
                                    <div><label class="block text-sm font-medium text-gray-700" for="branch_name_{{ $index }}">Branch</label><input id="branch_name_{{ $index }}" name="bank_details[{{ $index }}][branch_name]" value="{{ $bank['branch_name'] ?? '' }}" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-primary focus:ring-primary" /></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><x-input-label for="authorized_person" value="Authorized person" /><x-text-input id="authorized_person" name="authorized_person" class="mt-1 block w-full" :value="old('authorized_person', $setting->authorized_person ?? '')" /><x-input-error :messages="$errors->get('authorized_person')" class="mt-1" /></div>
                    <div><x-input-label for="invoice_footer" value="Invoice footer" /><textarea id="invoice_footer" name="invoice_footer" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 focus:border-primary focus:ring-primary">{{ old('invoice_footer', $setting->invoice_footer ?? '') }}</textarea><x-input-error :messages="$errors->get('invoice_footer')" class="mt-1" /></div>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-bold shadow-lg shadow-blue-600/20"><i class="fa-solid fa-floppy-disk"></i> Save settings</button>
            </div>
        </form>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('bankAccounts');
        const addButton = document.getElementById('addBankAccount');
        if (!container || !addButton) return;

        function renumberBanks() {
            container.querySelectorAll('.bank-account-row').forEach((row, index) => {
                row.dataset.bankIndex = index;
                row.querySelector('.bank-account-number').textContent = index + 1;
                row.querySelectorAll('input').forEach(input => {
                    input.name = input.name.replace(/bank_details\[\d+\]/, `bank_details[${index}]`);
                });
                row.querySelectorAll('label').forEach(label => {
                    label.htmlFor = label.htmlFor.replace(/_\d+$/, `_${index}`);
                });
                row.querySelectorAll('input').forEach(input => {
                    input.id = input.id.replace(/_\d+$/, `_${index}`);
                });
                row.querySelector('.remove-bank-account').classList.toggle('hidden', container.querySelectorAll('.bank-account-row').length === 1);
            });
        }

        addButton.addEventListener('click', function () {
            const index = container.querySelectorAll('.bank-account-row').length;
            const template = container.querySelector('.bank-account-row').cloneNode(true);
            template.querySelectorAll('input').forEach(input => {
                input.value = '';
                input.name = input.name.replace(/bank_details\[\d+\]/, `bank_details[${index}]`);
            });
            container.appendChild(template);
            renumberBanks();
        });

        container.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.remove-bank-account');
            if (!removeButton) return;
            removeButton.closest('.bank-account-row').remove();
            renumberBanks();
        });
    });
</script>

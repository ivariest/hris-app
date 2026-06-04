@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
        Ada beberapa field yang perlu dicek lagi.
    </div>
@endif

<form method="POST" action="{{ $formAction }}" class="space-y-6">
    @csrf
    @if ($candidate)
        @method('PUT')
    @endif

    <section class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="recruitment_request_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">New Employee Request <span class="text-error-500">*</span></label>
            <select id="recruitment_request_id" name="recruitment_request_id" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                <option value="">Pilih request</option>
                @foreach ($requests as $request)
                    <option value="{{ $request->id }}" @selected(old('recruitment_request_id', request('recruitment_request_id', $candidate->recruitment_request_id ?? '')) == $request->id)>{{ $request->request_number }} - {{ $request->requested_position }}</option>
                @endforeach
            </select>
            @error('recruitment_request_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        @if ($candidate)
            <div>
                <label for="category" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kategori <span class="text-error-500">*</span></label>
                <select id="category" name="category" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    @foreach ($categoryOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('category', $candidate->category ?? 'shortlist') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>
        @endif

        <div>
            <label for="candidate_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Kandidat <span class="text-error-500">*</span></label>
            <input type="text" id="candidate_name" name="candidate_name" value="{{ old('candidate_name', $candidate->candidate_name ?? '') }}" maxlength="150" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
            @error('candidate_name')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="id_card_number" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">No KTP</label>
            <input type="text" id="id_card_number" name="id_card_number" value="{{ old('id_card_number', $candidate->id_card_number ?? '') }}" maxlength="50" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
            @error('id_card_number')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="candidate_phone" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor Telepon Kandidat</label>
            <input type="text" id="candidate_phone" name="candidate_phone" value="{{ old('candidate_phone', $candidate->candidate_phone ?? '') }}" maxlength="30" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
            @error('candidate_phone')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="candidate_email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email Kandidat</label>
            <input type="email" id="candidate_email" name="candidate_email" value="{{ old('candidate_email', $candidate->candidate_email ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
            @error('candidate_email')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label for="candidate_address" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Alamat Kandidat</label>
            <textarea id="candidate_address" name="candidate_address" rows="3" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">{{ old('candidate_address', $candidate->candidate_address ?? '') }}</textarea>
            @error('candidate_address')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label for="psychotest_result" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Hasil Psikotes</label>
            <textarea id="psychotest_result" name="psychotest_result" rows="4" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">{{ old('psychotest_result', $candidate->psychotest_result ?? '') }}</textarea>
            @error('psychotest_result')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label for="comment" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Komentar</label>
            <textarea id="comment" name="comment" rows="4" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">{{ old('comment', $candidate->comment ?? '') }}</textarea>
            @error('comment')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>
    </section>

    <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
        <a href="{{ route('recruitment-candidates.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Batal</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">{{ $submitLabel }}</button>
    </div>
</form>

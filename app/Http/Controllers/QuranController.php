<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\QuranApiService; // Diubah untuk menggunakan service
use App\Models\User\Bookmark;    // Model Bookmark dari database
use App\Models\User\PersonalNote; // Model PersonalNote dari database
use App\Models\User; // Model User, pastikan ini path yang benar ke User model Anda
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Untuk logging
use Illuminate\Support\Facades\Session; // Masih bisa digunakan untuk pesan flash

class QuranController extends Controller
{
    protected $quranApiService;

    public function __construct(QuranApiService $quranApiService)
    {
        $this->quranApiService = $quranApiService;
        // Middleware 'auth' sudah diterapkan di routes/web.php
    }

    /**
     * Menampilkan halaman utama aplikasi Quran (daftar surah).
     */
    public function index()
    {
        $response = $this->quranApiService->getSurahList();
        $surahs = [];
        if (isset($response['success']) && $response['success']) {
            $surahs = $response['data'];
        } else {
            Log::error('Failed to get surah list for home page: ' . ($response['message'] ?? 'Unknown error from QuranApiService'));
            session()->flash('error', 'Gagal memuat daftar surah. Coba beberapa saat lagi.');
        }

        $lastRead = null;
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->last_read_surah && $user->last_read_ayah) {
                $surahName = $this->quranApiService->getSurahName((int)$user->last_read_surah);
                $lastRead = [
                    'surah_number' => $user->last_read_surah,
                    'ayah_number' => $user->last_read_ayah,
                    'surah_name' => $surahName ?? ('Surah ' . $user->last_read_surah)
                ];
            }
        }
            
        return view('user.quran.index', compact('surahs', 'lastRead'));
    }

    /**
     * Menampilkan detail sebuah surah.
     */
    public function showSurah(Request $request, $surahNumber)
    {
        $user = Auth::user();
        $editionIdentifier = session('active_translation_edition', config('quran_cloud.default_translation_edition', 'en.sahih'));

        $surahDataResponse = $this->quranApiService->getSurahDetail((int)$surahNumber, 'quran-uthmani');
        $translationDataResponse = $this->quranApiService->getSurahDetail((int)$surahNumber, $editionIdentifier);

        // Ambil data audio berdasarkan edition audio yang aktif
        $audioEditionIdentifier = session('active_audio_edition', config('alquran_cloud.default_audio_edition', 'ar.alafasy'));
        $audioDataResponse = $this->quranApiService->getSurahDetail((int)$surahNumber, $audioEditionIdentifier);

        if (!$surahDataResponse['success'] || !$translationDataResponse['success'] || !$audioDataResponse['success']) {
            Log::error("Failed to get surah/translation for surah {$surahNumber}.", [
                'surah_response' => $surahDataResponse,
                'translation_response' => $translationDataResponse
            ]);
            return redirect()->route('home')->with('error', 'Gagal memuat detail surah atau terjemahan.');
        }

        $surahDetails = $surahDataResponse['data'];
        $translationDetails = $translationDataResponse['data'];

        // Kumpulkan semua api_ayat_identifier untuk query batch
        $apiAyatIdentifiers = [];
        if (isset($surahDetails['ayahs']) && is_array($surahDetails['ayahs'])) {
            foreach ($surahDetails['ayahs'] as $ayah) {
                $apiAyatIdentifiers[] = $surahDetails['number'] . ':' . $ayah['numberInSurah'];
            }
        }

        // Ambil bookmark dan notes dalam satu query jika memungkinkan atau dua query terpisah
        $userBookmarks = Bookmark::where('user_id', $user->id)
                                ->whereIn('api_ayat_identifier', $apiAyatIdentifiers)
                                ->pluck('api_ayat_identifier')
                                ->flip(); // flip untuk pencarian cepat dengan isset()

        $userPersonalNotes = PersonalNote::where('user_id', $user->id)
                                    ->whereIn('api_ayat_identifier', $apiAyatIdentifiers)
                                    ->get()
                                    ->keyBy('api_ayat_identifier');


        if (isset($surahDetails['ayahs']) && is_array($surahDetails['ayahs'])) {
            foreach ($surahDetails['ayahs'] as $key => &$ayah) { // Pass by reference to modify
                $apiAyatIdentifier = $surahDetails['number'] . ':' . $ayah['numberInSurah'];
                $ayah['translation_text'] = $translationDetails['ayahs'][$key]['text'] ?? 'Terjemahan tidak tersedia.';
                // Tambahkan URL audio jika tersedia
                $ayah['audioUrl'] = $audioDataResponse['data']['ayahs'][$key]['audio'] ?? '';
                $ayah['is_bookmarked'] = isset($userBookmarks[$apiAyatIdentifier]);
                $ayah['personal_note'] = $userPersonalNotes->get($apiAyatIdentifier);
                $ayah['api_ayat_identifier'] = $apiAyatIdentifier; // Untuk digunakan di view
            }
        }
        
        // Simpan last read
        $user->last_read_surah = $surahDetails['number'];
        $user->last_read_ayah = 1; // Default ke ayat pertama surah yang dibuka
        $user->save();

        return view('user.quran.show_surat', compact('surahDetails', 'surahNumber'));
    }
    
    /**
     * Menampilkan detail satu ayat (mungkin tidak terlalu sering dipakai jika showSurah sudah lengkap).
     * Rute ini ada di web.php: Route::get('/ayah/{surah}/{ayah}', [QuranController::class, 'showAyah'])->name('ayah.show');
     */
    public function showAyah(Request $request, $surahNumber, $ayahNumberInSurah)
    {
        $user = Auth::user();
        $editionIdentifier = session('active_translation_edition', config('quran_cloud.default_translation_edition', 'en.sahih'));
        $apiAyatIdentifier = $surahNumber . ':' . $ayahNumberInSurah;

        $ayahDataResponse = $this->quranApiService->getAyatDetail((int)$surahNumber, (int)$ayahNumberInSurah, 'quran-uthmani');
        $translationDataResponse = $this->quranApiService->getAyatDetail((int)$surahNumber, (int)$ayahNumberInSurah, $editionIdentifier);

        if (!$ayahDataResponse['success'] || !$translationDataResponse['success']) {
            Log::error("Failed to get ayah details for {$apiAyatIdentifier}.", [
                'ayah_response' => $ayahDataResponse,
                'translation_response' => $translationDataResponse
            ]);
            return back()->with('error', 'Gagal memuat detail ayat atau terjemahan.');
        }

        $ayahDetails = $ayahDataResponse['data'];
        // Tambahkan teks terjemahan ke $ayahDetails
        $ayahDetails['translation_text'] = $translationDataResponse['data']['text'] ?? 'Terjemahan tidak tersedia.';
        
        // Cek bookmark & personal note
        $ayahDetails['is_bookmarked'] = Bookmark::where('user_id', $user->id)
                                             ->where('api_ayat_identifier', $apiAyatIdentifier)
                                             ->exists();
        $ayahDetails['personal_note'] = PersonalNote::where('user_id', $user->id)
                                                 ->where('api_ayat_identifier', $apiAyatIdentifier)
                                                 ->first();
        $ayahDetails['api_ayat_identifier'] = $apiAyatIdentifier;

        // Simpan last read
        $user->last_read_surah = (int)$surahNumber;
        $user->last_read_ayah = (int)$ayahNumberInSurah;
        $user->save();

        // Untuk view, mungkin lebih baik tetap menampilkan dalam konteks surah, atau view khusus ayat
        // Untuk sementara, kita bisa redirect ke showSurah dengan highlight ke ayat tersebut, atau buat view baru.
        // Jika menggunakan view yang sama dengan showSurah, Anda perlu sedikit modifikasi di view
        // return view('user.quran.show_ayah_detail', compact('ayahDetails'));
        // Atau, idealnya, redirect ke showSurah dan scroll ke ayat tersebut
        return redirect()->route('surah.show', ['number' => $surahNumber, 'highlight_ayah' => $ayahNumberInSurah])
                         ->with('ayahDetails', $ayahDetails); // Kirim detail ayat jika mau ditampilkan secara khusus
    }


    public function toggleBookmark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string|regex:/^\d+:\d+$/',
            'action' => 'required|string|in:add,remove',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $user = Auth::user();
            $identifier = $request->input('identifier');
            list($surahNumber, $ayahNumber) = explode(':', $identifier);

            if ($request->action == 'add') {
                Bookmark::create([
                    'user_id' => $user->id,
                    'api_ayat_identifier' => $identifier,
                    'surah_number' => $surahNumber,
                    'ayah_number' => $ayahNumber,
                ]);
                $message = 'Bookmark berhasil ditambahkan.';
                $isBookmarked = true;
            } else {
                Bookmark::where('user_id', $user->id)
                        ->where('api_ayat_identifier', $identifier)
                        ->delete();
                $message = 'Bookmark berhasil dihapus.';
                $isBookmarked = false;
            }

            return response()->json([
                'success' => true, 
                'message' => $message,
                'is_bookmarked' => $isBookmarked
            ]);

        } catch (\Exception $e) {
            Log::error("Bookmark toggle failed for user {$user->id} and identifier {$identifier}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error while toggling bookmark.'], 500);
        }
    }

    public function showBookmarks()
    {
        $user = Auth::user();
        $bookmarks = Bookmark::where('user_id', $user->id)
                                ->orderBy('created_at', 'desc')
                                ->get();
        
        return view('user.bookmarks.index', compact('bookmarks'));
    }

    // --- Personal Notes ---
    public function showNotes()
    {
        try {
            $user = Auth::user();
            $notes = PersonalNote::where('user_id', $user->id)
                               ->orderBy('updated_at', 'desc')
                               ->get();

            return view('user.notes.index', ['notes' => $notes]);
            
        } catch (\Exception $e) {
            \Log::error('Error in showNotes: ' . $e->getMessage());
            return view('user.notes.index', [
                'notes' => collect(), // Pass an empty collection on error
                'error' => 'Terjadi kesalahan saat memuat catatan.'
            ]);
        }
    }

    public function addNote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'api_ayat_identifier' => 'required|string|regex:/^\d+:\d+$/',
            'note' => 'required|string|max:5000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $user = Auth::user();
            $note = PersonalNote::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'api_ayat_identifier' => $request->api_ayat_identifier
                ],
                ['note' => $request->note]
            );

            return response()->json([
                'success' => true,
                'message' => $note->wasRecentlyCreated ? 'Catatan berhasil ditambahkan.' : 'Catatan berhasil diperbarui.',
                'note' => $note
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in addNote: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan catatan'
            ], 500);
        }
    }
    
    // Rute GET untuk form edit: quran.notes.editForm
    // Route::get('/quran/surah/{surahNumber}/ayah/{ayahNumberInSurah}/notes/{noteId}/edit', [QuranController::class, 'editAyahNoteForm'])->name('quran.notes.editForm');
    public function editAyahNoteForm(Request $request, $surahNumber, $ayahNumberInSurah, PersonalNote $note) // Route model binding
    {
        $user = Auth::user();
        // Pastikan note milik user
        if ($note->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        // Ambil detail ayat untuk konteks
        $apiAyatIdentifier = $surahNumber . ':' . $ayahNumberInSurah;
        $ayahDataResponse = $this->quranApiService->getAyatDetail((int)$surahNumber, (int)$ayahNumberInSurah, 'quran-uthmani');
        $ayahDetails = null;
        if($ayahDataResponse['success']){
            $ayahDetails = $ayahDataResponse['data'];
        } else {
            Log::warning("Gagal mengambil detail ayat {$apiAyatIdentifier} untuk form edit catatan.");
        }

        // Perlu view khusus atau bisa pakai modal di show_surat
        return view('user.notes.edit_form_page', compact('note', 'ayahDetails', 'surahNumber', 'ayahNumberInSurah'));
    }


    // Rute PUT untuk update note: quran.notes.update (menggunakan noteId)
    // Route::put('/quran/notes/{noteId}', [QuranController::class, 'updateAyahNote'])->name('quran.notes.update');
    public function updateAyahNote(Request $request, PersonalNote $note) // Route model binding untuk $note
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
             if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        if ($note->user_id !== $user->id) {
             if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
            return back()->with('error', 'Akses ditolak.');
        }

        $note->update(['note' => $request->note]);
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Catatan berhasil diperbarui.', 'note' => $note]);
        }
        return redirect()->route('notes.show')->with('success', 'Catatan berhasil diperbarui.');
    }

    // Rute DELETE untuk hapus note: quran.notes.delete
    // Route::delete('/quran/notes/{noteId}', [QuranController::class, 'destroyAyahNote'])->name('quran.notes.delete');
    public function destroyAyahNote(Request $request, PersonalNote $note) // Route model binding
    {
        $user = Auth::user();
        if ($note->user_id !== $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
            return back()->with('error', 'Akses ditolak.');
        }

        $note->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Catatan berhasil dihapus.']);
        }
        return redirect()->route('notes.show')->with('success', 'Catatan berhasil dihapus.');
    }
    
    /**
     * Rute GET untuk form edit (jika dari halaman daftar notes): notes.edit
     * Route::get('/notes/{id}/edit', [QuranController::class, 'editNote'])->name('notes.edit');
     * $id di sini adalah ID PersonalNote
     */
    public function editNote(Request $request, PersonalNote $note) // Route model binding
    {
        $user = Auth::user();
        if ($note->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        list($surahNum, $ayahNumInSurah) = explode(':', $note->api_ayat_identifier);
        
        $ayahDataResponse = $this->quranApiService->getAyatDetail((int)$surahNum, (int)$ayahNumInSurah, 'quran-uthmani');
        $ayahDetails = null;
        if($ayahDataResponse['success']){
            $ayahDetails = $ayahDataResponse['data'];
        } else {
            Log::warning("Gagal mengambil detail ayat {$note->api_ayat_identifier} untuk form edit catatan.");
        }

        return view('user.notes.edit', compact('note', 'ayahDetails')); // View yang berbeda dari editAyahNoteForm
    }

    /**
     * Rute PUT untuk update note (jika dari halaman daftar notes): notes.update
     * Route::put('/notes/{id}', [QuranController::class, 'updateNote'])->name('notes.update');
     * $id di sini adalah ID PersonalNote
     */
    public function updateNote(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required|string|max:5000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $note = PersonalNote::where('user_id', Auth::id())->findOrFail($id);
            $note->update(['note' => $request->note]);

            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil diperbarui',
                'note' => $note
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in updateNote: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui catatan'
            ], 500);
        }
    }

    /**
     * Rute DELETE untuk hapus note (jika dari halaman daftar notes): notes.delete
     * Route::delete('/notes/{id}', [QuranController::class, 'deleteNote'])->name('notes.delete');
     */
    public function deleteNote($id)
    {
        try {
            $note = PersonalNote::where('user_id', Auth::id())->findOrFail($id);
            $note->delete();

            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in deleteNote: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus catatan'
            ], 500);
        }
    }

    // Menyimpan posisi terakhir dibaca (last read)
    public function saveLastRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'surah_number' => 'required|integer|min:1|max:114',
            'ayah_number' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();
        $user->last_read_surah = $request->surah_number;
        $user->last_read_ayah = $request->ayah_number;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Posisi terakhir dibaca disimpan.']);
    }

    // Menghapus posisi terakhir dibaca (last read)
    public function deleteLastRead(Request $request)
    {
        try {
            $user = Auth::user();
            $user->last_read_surah = null;
            $user->last_read_ayah = null;
            $user->save();

            return response()->json(['success' => true, 'message' => 'Posisi terakhir dibaca berhasil dihapus.']);
        } catch (\Exception $e) {
            \Log::error("Failed to delete last read for user {" . optional(Auth::user())->id . "}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus posisi terakhir dibaca.'], 500);
        }
    }

    public function saveNote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string|regex:/^\d+:\d+$/',
            'content'    => 'required|string|max:5000',
            'note_id'    => 'nullable|integer|exists:personal_notes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $user = Auth::user();

            $note = PersonalNote::updateOrCreate(
                [
                    'id'      => $request->note_id,
                    'user_id' => $user->id,
                    'api_ayat_identifier' => $request->identifier,
                ],
                [
                    'note' => $request->content,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil disimpan.',
                'note_id' => $note->id
            ]);

        } catch (\Exception $e) {
            Log::error("Note saving failed for user {" . optional(Auth::user())->id . "}, identifier {$request->identifier}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal saat menyimpan catatan.'], 500);
        }
    }
}
 
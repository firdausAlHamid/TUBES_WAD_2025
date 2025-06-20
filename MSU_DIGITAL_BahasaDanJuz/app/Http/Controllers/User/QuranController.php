<?php

namespace App\Http\Controllers\User;

use App\Models\Juz;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Models\FavoriteSurat;
use App\Models\User\Bookmark;
use App\Models\User\PersonalNote;
use App\Services\QuranApiService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class QuranController extends Controller
{
    protected $quranApiService;
    
    public function __construct(QuranApiService $quranApiService)
    {
        $this->quranApiService = $quranApiService;
    }

    public function index()
    {
        try {
            // Get surah list from API
            $response = $this->quranApiService->getSurahList();

            if (!$response['success']) {
                throw new \Exception($response['message'] ?? 'Failed to fetch surah list');
            }

            // Get user's last read position
            $lastRead = $this->getLastReadPosition();
            $languages = Language::all();

            // Return the view with data
            return view('user.quran.index', [
                'surahs' => $response['data'],
                'lastRead' => $lastRead,
                'languages' => $languages,
            ]);

        } catch (\Exception $e) {
            return view('user.quran.index', [
                'surahs' => [],
                'lastRead' => null,
                'error' => 'Gagal memuat daftar surat. Silakan coba lagi nanti.'
            ]);
        }
    }

    protected function getLastReadPosition()
    {
        $user = Auth::user();
        
        if (!$user->last_read_surah || !$user->last_read_ayah) {
            return null;
        }

        try {
            $surahDetail = $this->quranApiService->getSurahDetail($user->last_read_surah);
            
            if (!$surahDetail['success']) {
                return null;
            }

            return [
                'surah_number' => $user->last_read_surah,
                'surah_name' => $surahDetail['data']['name'],
                'ayah_number' => $user->last_read_ayah
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function showSurat($number)
    {
        try {
            $user = Auth::id();
            $editionIdentifier = session('active_translation_edition', config('quran_cloud.default_translation_edition', 'id.indonesian'));
            $audioEdition = 'ar.alafasy'; // Menggunakan Mishary Rashid Al-Afasy sebagai default

            \Log::info('Fetching surah data', ['number' => $number, 'edition' => $editionIdentifier]);

            $surahDataResponse = $this->quranApiService->getSurahDetail($number, 'quran-uthmani');
            $translationDataResponse = $this->quranApiService->getSurahDetail($number, $editionIdentifier);
            $audioDataResponse = $this->quranApiService->getSurahDetail($number, $audioEdition);

            \Log::info('API responses', [
                'surahData' => $surahDataResponse,
                'translationData' => $translationDataResponse,
                'audioData' => $audioDataResponse
            ]);

            if (!$surahDataResponse['success'] || !$translationDataResponse['success'] || !$audioDataResponse['success']) {
                throw new \Exception('Failed to fetch surah data or translation: ' . 
                    ($surahDataResponse['message'] ?? '') . ' ' . 
                    ($translationDataResponse['message'] ?? '') . ' ' .
                    ($audioDataResponse['message'] ?? ''));
            }

            $surahDetails = $surahDataResponse['data'];
            $translationDetails = $translationDataResponse['data'];
            $audioDetails = $audioDataResponse['data'];

            // Process ayahs with translations, audio, and user data
            if (isset($surahDetails['ayahs']) && is_array($surahDetails['ayahs'])) {
                foreach ($surahDetails['ayahs'] as $key => &$ayah) {
                    $apiAyatIdentifier = $surahDetails['number'] . ':' . $ayah['numberInSurah'];
                    
                    $ayah['translation_text'] = $translationDetails['ayahs'][$key]['text'] ?? 'Terjemahan tidak tersedia';
                    $ayah['audioUrl'] = $audioDetails['ayahs'][$key]['audio'] ?? null;
                    $ayah['is_bookmarked'] = Bookmark::where('user_id', $user)
                                                ->where('api_ayat_identifier', $apiAyatIdentifier)
                                                ->exists();
                    $ayah['personal_note'] = PersonalNote::where('user_id', $user)
                                                    ->where('api_ayat_identifier', $apiAyatIdentifier)
                                                    ->first();
                    $ayah['api_ayat_identifier'] = $apiAyatIdentifier;
                }
            }

            // Update last read position
            $this->updateLastRead($number, 1);
            
            \Log::info('Rendering view with data', ['surahDetails' => $surahDetails]);
            
            return view('user.quran.show_surat', compact('surahDetails'));
        } catch (\Exception $e) {
            \Log::error('Error in showSurat', [
                'number' => $number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Gagal memuat detail surah. Silakan coba lagi nanti. Error: ' . $e->getMessage());
        }
    }

    public function showBookmarks()
    {
        $user = Auth::user();
        $bookmarks = Bookmark::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        $bookmarksData = [];
        foreach ($bookmarks as $bookmark) {
            list($surahNum, $ayahNum) = explode(':', $bookmark->api_ayat_identifier);
            
            try {
                $surahDetail = $this->quranApiService->getSurahDetail($surahNum);
                if ($surahDetail['success']) {
                    $bookmarksData[] = [
                        'id' => $bookmark->id,
                        'surah_number' => $surahNum,
                        'ayah_number' => $ayahNum,
                        'surah_name' => $surahDetail['data']['name'],
                        'created_at' => $bookmark->created_at
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return view('user.bookmarks.index', ['bookmarks' => $bookmarksData]);
    }
    public function juz(){
        return view('user.juz.index');
    }

    public function addJuz(Request $request){
        $juz=Juz::create([
            'name'=>$request->nama_juz,
            'first_surah'=>$request->surat_awal,
            'last_surah'=>$request->surat_akhir
        ]);
        if($juz){
            return redirect()->route('juz.add')->with('success', 'Juz berhasil ditambahkan'); 
        }else{
            return redirect()->route('juz.add')->with('error', 'Gagal menambahkan Juz. Silakan coba lagi nanti.');
        }
    }
    public function showJuzList(){
        $juzList = Juz::all();
        return view('user.juz.juzList',compact('juzList'));
    }
    public function EditJuz($id){
        $juz = Juz::findOrFail($id);
        return view('user.juz.edit',compact('juz'));
    }
    public function updateJuz(Request $request, $id){
        $juz = Juz::findOrFail($id);
        $juz->name = $request->nama_juz;
        $juz->first_surah = $request->surat_awal;
        $juz->last_surah = $request->surat_akhir;
        if($juz->save()){
            return redirect()->route('juz.list')->with('success', 'Juz berhasil diperbarui');
        }else{
            return back()->with('error', 'Gagal memperbarui Juz. Silakan coba lagi nanti.');
        }
    }
    public function deleteJuz($id){
        $juz = Juz::findOrFail($id);
        if($juz->delete()){
            return redirect()->route('juz.list')->with('success', 'Juz berhasil dihapus');
        }else{
            return redirect()->route('juz.list')->with('error', 'Gagal menghapus Juz. Silakan coba lagi nanti.');
        }
    }
    public function showJuz($juz)
    {
        try {
            $user = Auth::id();
            $editionIdentifier = session('active_translation_edition', 'id.indonesian');

            \Log::info('Fetching juz data', ['juz' => $juz, 'edition' => $editionIdentifier]);

            // Get original Arabic text
            $juzDataResponse = $this->quranApiService->getJuz($juz, 'quran-uthmani');
            
            // Get translation in selected language

            \Log::info('API responses', [
                'juzData' => $juzDataResponse,
            ]);

            if (!$juzDataResponse['success']) {
                throw new \Exception('Failed to fetch juz data');
            }
            
            $juzDetails = $juzDataResponse['data'];
            
            
            return view('user.juz.juz', ['juz' => $juzDetails]);

        } catch (\Exception $e) {
            \Log::error('Error in showJuz', [
                'juz' => $juz,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Gagal memuat detail juz. Silakan coba lagi nanti. Error: ' . $e->getMessage());
        }
    }

    public function showNotes()
    {
        $user = Auth::user();
        $notes = PersonalNote::where('user_id', $user->id)
                           ->orderBy('updated_at', 'desc')
                           ->get();

        $notesData = [];
        foreach ($notes as $note) {
            list($surahNum, $ayahNum) = explode(':', $note->api_ayat_identifier);
            
            try {
                $surahDetail = $this->quranApiService->getSurahDetail($surahNum);
                if ($surahDetail['success']) {
                    $notesData[] = [
                        'id' => $note->id,
                        'surah_number' => $surahNum,
                        'ayah_number' => $ayahNum,
                        'surah_name' => $surahDetail['data']['name'],
                        'note_content' => $note->note,
                        'updated_at' => $note->updated_at
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return view('user.notes.index', ['notes' => $notesData]);
    }

    public function toggleBookmark(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'api_ayat_identifier' => 'required|string|regex:/^\d+:\d+$/'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $user = Auth::user();
            $bookmark = Bookmark::where('user_id', $user->id)
                              ->where('api_ayat_identifier', $request->api_ayat_identifier)
                              ->first();

            if ($bookmark) {
                $bookmark->delete();
                $message = 'Bookmark berhasil dihapus';
                $isBookmarked = false;
            } else {
                Bookmark::create([
                    'user_id' => $user->id,
                    'api_ayat_identifier' => $request->api_ayat_identifier
                ]);
                $message = 'Bookmark berhasil ditambahkan';
                $isBookmarked = true;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'is_bookmarked' => $isBookmarked
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in toggleBookmark: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses bookmark'
            ], 500);
        }
    }

    protected function updateLastRead($surahNumber, $ayahNumber)
    {
        try {
            $user = Auth::user();
            $user->last_read_surah = $surahNumber;
            $user->last_read_ayah = $ayahNumber;
            $user->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function saveLastRead(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'surah_number' => 'required|integer|min:1|max:114',
            'ayah_number' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $this->updateLastRead($request->surah_number, $request->ayah_number);
            return response()->json(['success' => true, 'message' => 'Posisi terakhir dibaca disimpan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan posisi terakhir dibaca.']);
        }
    }

    public function addNote(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'api_ayat_identifier' => 'required|string|regex:/^\d+:\d+$/',
            'note' => 'required|string|max:1000'
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
                'message' => 'Catatan berhasil disimpan',
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

    public function editNote($id)
    {
        $note = PersonalNote::where('user_id', Auth::id())->findOrFail($id);
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'note' => $note]);
        }
        
        return view('user.notes.edit', compact('note'));
    }

    public function updateNote(Request $request, $id)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'note' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $note = PersonalNote::where('user_id', Auth::id())->findOrFail($id);
            $note->note = $request->note;
            $note->save();

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

    // Tampilkan daftar bahasa
    public function languageIndex()
    {
        $languages = Language::all();
        return view('user.language.index', compact('languages'));
    }

    // Tampilkan form tambah bahasa
    public function languageCreate()
    {
        return view('user.language.create');
    }

    // Simpan bahasa baru
    public function languageStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'edition' => 'required',
            'code' => 'nullable'
        ]);
        Language::create($request->only('name', 'edition', 'code'));
        return redirect()->route('languages.index')->with('success', 'Bahasa berhasil ditambahkan');
    }

    // Tampilkan form edit bahasa
    public function languageEdit($id)
    {
        $language = Language::findOrFail($id);
        return view('user.language.edit', compact('language'));
    }

    // Update bahasa
    public function languageUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'edition' => 'required',
            'code' => 'nullable'
        ]);
        $language = Language::findOrFail($id);
        $language->update($request->only('name', 'edition', 'code'));
        return redirect()->route('languages.index')->with('success', 'Bahasa berhasil diupdate');
    }

    // Hapus bahasa
    public function languageDestroy($id)
    {
        $language = Language::findOrFail($id);
        $language->delete();
        return redirect()->route('languages.index')->with('success', 'Bahasa berhasil dihapus');
    }
}

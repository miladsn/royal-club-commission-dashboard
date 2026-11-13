<?php


use App\Models\Exchange;
use App\Services\Exchanges\Bitunix\BitunixProvider;
use App\Services\Exchanges\Bitunix\FakeExcelGenerator;
use App\Services\Exchanges\ExchangeSyncDispatcher;
use App\Services\Exchanges\LBank\LBankProvider;
use App\Services\Exchanges\Orbit\OrbitProvider;
use App\Services\FakeExchangePayloadGenerator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Exports\BitunixFakeReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Services\Ai\ExchangeAiService;

Route::get('/', function () {
    return view('welcome');
});


// ==========================================
// Mock Excel download section (form and direct download)
// ==========================================

Route::get('/test/bitunix/excel-form', function () {
    return '
    <form action="/test/bitunix/download-excel" method="GET" style="font-family:Tahoma; direction:rtl; padding:20px;">
        <h2>دریافت فایل اکسل فرضی بیتونیکس (برای تست تسترها)</h2>
        <label>تعداد کاربران:</label><br>
        <input type="number" name="user_count" value="15"><br><br>

        <label>تعداد گزارش‌ها:</label><br>
        <input type="number" name="report_count" value="30"><br><br>

        <label>تاریخ شروع:</label><br>
        <input type="date" name="start_date" value="' . now()->subDays(30)->toDateString() . '"><br><br>

        <label>تاریخ پایان:</label><br>
        <input type="date" name="end_date" value="' . now()->toDateString() . '"><br><br>

        <button type="submit" style="padding:10px 20px; background:green; color:white; border:none; cursor:pointer;">ساخت و دانلود اکسل</button>
    </form>
    ';
});

Route::get('/test/bitunix/download-excel', function (Request $request) {
    $userCount = $request->input('user_count', 15);
    $reportCount = $request->input('report_count', 30);
    $startDate = $request->input('start_date') ? $request->input('start_date') . ' 00:00:00' : null;
    $endDate = $request->input('end_date') ? $request->input('end_date') . ' 23:59:59' : null;

    $generator = new \App\Services\FakeExchangePayloadGenerator();
    $fake = $generator->generate('bitunix', (int)$userCount, (int)$reportCount, $startDate, $endDate);
    $rows = $fake['payload']['commissions'] ?? [];

    $fileName = 'bitunix-report-' . now()->format('Ymd_His') . '.xlsx';

    // Download the Excel file directly instead of saving it on the server
    return Excel::download(new BitunixFakeReportExport($rows), $fileName);
});


Route::post('/ai/chat', function (Request $request, ExchangeAiService $aiService) {

    $validated = $request->validate([
        'question' => ['nullable', 'string', 'max:5000'],
        'slug' => ['nullable', 'string', 'max:100'],
        'filters' => ['nullable', 'array'],
        'filters.exchange_id' => ['nullable', 'integer', 'min:1'],
        'filters.status' => ['nullable', 'in:matched,unmatched,unknown_uid'],
        'filters.uids' => ['nullable', 'string', 'max:5000'],
        'filters.date_from' => ['nullable', 'date_format:Y-m-d'],
        'filters.date_to' => ['nullable', 'date_format:Y-m-d'],
    ]);
    $filters = array_filter($validated['filters'] ?? [], fn ($value) => $value !== null && $value !== '');
    if (isset($filters['uids'])) {
        $filters['uids'] = array_values(array_unique(array_filter(
            array_map('trim', preg_split('/[,;\r\n]+/', $filters['uids'])),
            fn ($uid) => $uid !== ''
        )));
        if ($filters['uids'] === []) {
            unset($filters['uids']);
        }
    }

    // If the request specifies a predefined question by 'slug'
    if ($request->has('slug')) {
        $slug = $request->input('slug');
        $answer = $aiService->handlePredefinedQuestion($slug, $filters);
        return response()->json([
            'success' => true,
            'slug' => $slug,
            'answer' => $answer,
            'answer_html' => Str::markdown($answer, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
                'renderer' => ['soft_break' => '<br />'],
            ])
        ]);
    }

    // Normal free-form chat mode
    $question = $request->input('question', 'مجموع کل کمیسیون‌ها و حجم معاملات چقدر است؟');
    $answer = $aiService->chatWithAi($question, $filters);
    return response()->json([
        'success' => true,
        'question' => $question,
        'answer' => $answer,
        'answer_html' => Str::markdown($answer, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'renderer' => ['soft_break' => '<br />'],
        ])
    ]);
})->middleware('auth')->name('ai.chat');

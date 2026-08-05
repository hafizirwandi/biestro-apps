<?php

use Illuminate\Support\Facades\Auth;
use UniSharp\LaravelFilemanager\Lfm;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AuditTraceController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\FreeGiftRuleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketPackageController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WahanaController;
use App\Http\Controllers\PrinterTestController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\PlaygroundController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', [AuthController::class, 'loginFormAdmin'])->name('login');
Route::post('/auth', [AuthController::class, 'auth'])->name('auth');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        if ($user->hasRole('cashier')) {
            return redirect('/transaction');
        }

        if ($user->hasRole('scan')) {
            return redirect('/scan');
        }

        if ($user->hasRole('playground')) {
            return redirect('/playground');
        }

        return redirect('/home');
    }

    return redirect('/login')->withErrors([
        'msg' => 'Please log in to continue your session',
    ]);
});

Route::middleware('auth:web')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])
        ->name('home')
        ->middleware('can:dashboard');
    Route::get('/dashboard/stat', [HomeController::class, 'stat'])
        ->name('dashboard.stat')
        ->middleware('can:dashboard');

    Route::get('/user', [UserController::class, 'index'])
        ->name('user')
        ->middleware('can:user-list');
    Route::get('/user/create', [UserController::class, 'create'])
        ->name('user.create')
        ->middleware('can:user-create');
    Route::get('/user/edit/{id}', [UserController::class, 'edit'])
        ->name('user.edit')
        ->middleware('can:user-edit');
    Route::post('/user', [UserController::class, 'saveOrUpdate'])
        ->name('user.store')
        ->middleware('can:user-create');
    Route::put('/user/{id}', [UserController::class, 'saveOrUpdate'])
        ->name('user.update')
        ->middleware('can:user-edit');
    Route::delete('/user/delete', [UserController::class, 'destroy'])
        ->name('user.destroy')
        ->middleware('can:user-delete');

    Route::get('/permission', [PermissionController::class, 'index'])
        ->name('permission')
        ->middleware('can:permission-list');
    Route::get('/permission/create', [PermissionController::class, 'create'])
        ->name('permission.create')
        ->middleware('can:permission-create');
    Route::get('/permission/edit/{id}', [PermissionController::class, 'edit'])
        ->name('permission.edit')
        ->middleware('can:permission-edit');
    Route::post('/permission', [PermissionController::class, 'saveOrUpdate'])
        ->name('permission.store')
        ->middleware('can:permission-create');
    Route::put('/permission/{id}', [PermissionController::class, 'saveOrUpdate'])
        ->name('permission.update')
        ->middleware('can:permission-edit');
    Route::delete('/permission/delete', [PermissionController::class, 'destroy'])
        ->name('permission.destroy')
        ->middleware('can:permission-delete');

    Route::get('/role', [RoleController::class, 'index'])
        ->name('role')
        ->middleware('can:role-list');
    Route::get('/role/create', [RoleController::class, 'create'])
        ->name('role.create')
        ->middleware('can:role-create');
    Route::get('/role/detail/{id}', [RoleController::class, 'detail'])
        ->name('role.detail')
        ->middleware('can:role-add-permission');
    Route::get('/role/edit/{id}', [RoleController::class, 'edit'])
        ->name('role.edit')
        ->middleware('can:role-edit');
    Route::post('/role', [RoleController::class, 'saveOrUpdate'])
        ->name('role.store')
        ->middleware('can:role-create');
    Route::post('/role/savePermission', [RoleController::class, 'savePermission'])
        ->name('role.savePermission')
        ->middleware('can:role-add-permission');
    Route::put('/role/{id}', [RoleController::class, 'saveOrUpdate'])
        ->name('role.update')
        ->middleware('can:role-edit');
    Route::delete('/role/delete', [RoleController::class, 'destroy'])
        ->name('role.destroy')
        ->middleware('can:role-delete');

    Route::prefix('wahana')->group(function () {
        Route::get('/', [WahanaController::class, 'index'])
            ->name('wahana')
            ->middleware('can:wahana-list');
        Route::get('/create', [WahanaController::class, 'create'])
            ->name('wahana.create')
            ->middleware('can:wahana-create');
        Route::get('/edit/{id}', [WahanaController::class, 'edit'])
            ->name('wahana.edit')
            ->middleware('can:wahana-edit');
        Route::post('/', [WahanaController::class, 'saveOrUpdate'])
            ->name('wahana.store')
            ->middleware('can:wahana-create');
        Route::put('/{id}', [WahanaController::class, 'saveOrUpdate'])
            ->name('wahana.update')
            ->middleware('can:wahana-edit');
        Route::delete('/delete', [WahanaController::class, 'destroy'])
            ->name('wahana.destroy')
            ->middleware('can:wahana-delete');
        Route::get('/get-data', [WahanaController::class, 'getData'])
            ->name('wahana.get-data')
            ->middleware('can:wahana-list');
    });

    Route::prefix('counter')->group(function () {
        Route::get('/', [CounterController::class, 'index'])
            ->name('counter')
            ->middleware('can:counter-list');
        Route::get('/create', [CounterController::class, 'create'])
            ->name('counter.create')
            ->middleware('can:counter-create');
        Route::get('/edit/{id}', [CounterController::class, 'edit'])
            ->name('counter.edit')
            ->middleware('can:counter-edit');
        Route::post('/', [CounterController::class, 'saveOrUpdate'])
            ->name('counter.store')
            ->middleware('can:counter-create');
        Route::put('/{id}', [CounterController::class, 'saveOrUpdate'])
            ->name('counter.update')
            ->middleware('can:counter-edit');
        Route::delete('/delete', [CounterController::class, 'destroy'])
            ->name('counter.destroy')
            ->middleware('can:counter-delete');
    });

    Route::prefix('ticket')->group(function () {
        Route::get('/', [TicketController::class, 'index'])
            ->name('ticket')
            ->middleware('can:ticket-list');
        Route::get('/create', [TicketController::class, 'create'])
            ->name('ticket.create')
            ->middleware('can:ticket-create');
        Route::get('/edit/{id}', [TicketController::class, 'edit'])
            ->name('ticket.edit')
            ->middleware('can:ticket-edit');
        Route::post('/', [TicketController::class, 'saveOrUpdate'])
            ->name('ticket.store')
            ->middleware('can:ticket-create');
        Route::put('/{id}', [TicketController::class, 'saveOrUpdate'])
            ->name('ticket.update')
            ->middleware('can:ticket-edit');
        Route::delete('/delete', [TicketController::class, 'destroy'])
            ->name('ticket.destroy')
            ->middleware('can:ticket-delete');
    });

    Route::prefix('ticket-package')->group(function () {
        Route::get('/', [TicketPackageController::class, 'index'])
            ->name('ticket-package')
            ->middleware('can:ticket-package-list');
        Route::get('/create', [TicketPackageController::class, 'create'])
            ->name('ticket-package.create')
            ->middleware('can:ticket-package-create');
        Route::get('/edit/{id}', [TicketPackageController::class, 'edit'])
            ->name('ticket-package.edit')
            ->middleware('can:ticket-package-edit');
        Route::post('/', [TicketPackageController::class, 'saveOrUpdate'])
            ->name('ticket-package.store')
            ->middleware('can:ticket-package-create');
        Route::put('/{id}', [TicketPackageController::class, 'saveOrUpdate'])
            ->name('ticket-package.update')
            ->middleware('can:ticket-package-edit');
        Route::delete('/delete', [TicketPackageController::class, 'destroy'])
            ->name('ticket-package.destroy')
            ->middleware('can:ticket-package-delete');
    });

    Route::prefix('free-gift')->group(function () {
        Route::get('/', [FreeGiftRuleController::class, 'index'])
            ->name('free-gift')
            ->middleware('can:free-gift-list');
        Route::get('/create', [FreeGiftRuleController::class, 'create'])
            ->name('free-gift.create')
            ->middleware('can:free-gift-create');
        Route::get('/edit/{id}', [FreeGiftRuleController::class, 'edit'])
            ->name('free-gift.edit')
            ->middleware('can:free-gift-edit');
        Route::post('/', [FreeGiftRuleController::class, 'saveOrUpdate'])
            ->name('free-gift.store')
            ->middleware('can:free-gift-create');
        Route::put('/{id}', [FreeGiftRuleController::class, 'saveOrUpdate'])
            ->name('free-gift.update')
            ->middleware('can:free-gift-edit');
        Route::delete('/delete', [FreeGiftRuleController::class, 'destroy'])
            ->name('free-gift.destroy')
            ->middleware('can:free-gift-delete');
    });

    Route::prefix('transaction')
        ->middleware('can:pos')
        ->group(function () {
            Route::get('/', [TransactionController::class, 'index'])->name('transaction');
            Route::post('/store', [TransactionController::class, 'store'])->name('transaction.store');
            Route::get('/view/{id}', [TransactionController::class, 'view'])->name('transaction.view');
            Route::get('/view-partial/{id}', [TransactionController::class, 'viewPartial'])->name('transaction.view-partial');
            Route::post('/print-bill', [TransactionController::class, 'printBill'])->name('transaction.print-bill');
            Route::post('/print-ticket', [TransactionController::class, 'printTicket'])->name('transaction.print-ticket');
            Route::post('/print-ticket-all', [TransactionController::class, 'printTicketAll'])->name('transaction.print-ticket-all');
            Route::get('/get-detail/{id}', [TransactionController::class, 'getDetail'])->name('transaction.get-detail');
            Route::get('/check-free/{id}', [TransactionController::class, 'checkFree'])->name('transaction.check-free');
            Route::get('/open-shift', [TransactionController::class, 'openShift'])->name('transaction.open-shift');
            Route::get('/close-shift', [TransactionController::class, 'closeShift'])->name('transaction.close-shift');
            Route::get('/sales-revenue', [TransactionController::class, 'salesRevenue'])->name('transaction.sales-revenue');
            Route::get('/ticket-sold', [TransactionController::class, 'ticketSold'])->name('transaction.ticket-sold');
            Route::get('/wahana-sold', [TransactionController::class, 'wahanaSold'])->name('transaction.wahana-sold');
            Route::get('/reprint-receipt', [TransactionController::class, 'reprintReceipt'])->name('transaction.reprint-receipt');
            Route::get('/close', [TransactionController::class, 'close'])->name('transaction.close');
            Route::get('/delete-draft-transaction/{id}', [TransactionController::class, 'deleteDraftTransaction'])->name('transaction.delete-draft-transaction');
            Route::post('/save-draft', [TransactionController::class, 'saveDraft'])->name('transaction.save-draft');
            Route::get('/pending-list', [TransactionController::class, 'pendingList'])->name('transaction.pending-list');
            Route::get('/pending-detail/{id}', [TransactionController::class, 'pendingDetail'])->name('transaction.pending-detail');

            Route::post('/set-open-shift', [TransactionController::class, 'setOpenShift'])->name('transaction.set-open-shift');
            Route::post('/set-open-shift', [TransactionController::class, 'setOpenShift'])->name('transaction.set-open-shift');
            Route::post('/set-close-shift', [TransactionController::class, 'setCloseShift'])->name('transaction.set-close-shift');
            Route::post('/reprint', [TransactionController::class, 'reprint'])->name('transaction.reprint');
            Route::post('/void', [TransactionController::class, 'voidTransaction'])->name('transaction.void');
            Route::get('/shift-data', [TransactionController::class, 'getShiftData'])->name('transaction.shift-data');
        });

    Route::prefix('scan')
        ->middleware('can:scan-access')
        ->group(function () {
            Route::get('/', [ScanController::class, 'index'])->name('scan');
            Route::post('/', [ScanController::class, 'scan'])->name('scan.scan');
            Route::post('/unflag', [ScanController::class, 'unflag'])->name('scan.unflag');
        });

    Route::prefix('playground')
        ->middleware('can:playground-access')
        ->group(function () {
            Route::get('/', [PlaygroundController::class, 'index'])->name('playground');
            Route::post('/store', [PlaygroundController::class, 'store'])->name('playground.store')->middleware('can:playground-input');
            Route::get('/active-sessions', [PlaygroundController::class, 'activeSessions'])->name('playground.active-sessions');
            Route::post('/{id}/call-manual', [PlaygroundController::class, 'callManual'])->name('playground.call-manual');
            Route::post('/{id}/stop-calling', [PlaygroundController::class, 'stopCalling'])->name('playground.stop-calling');
            Route::post('/{id}/finish', [PlaygroundController::class, 'finish'])->name('playground.finish');
            Route::get('/report', [PlaygroundController::class, 'report'])->name('playground.report')->middleware('can:playground-report');
        });

    Route::prefix('report')->group(function () {
        Route::get('/transaction', [ReportController::class, 'transaction'])->name('report.transaction');
        Route::get('/ticket', [ReportController::class, 'ticket'])->name('report.ticket');
        Route::get('/shift', [ReportController::class, 'shift'])->name('report.shift');
        Route::get('/items', [ReportController::class, 'items'])->name('report.items');
        Route::get('/payment', [ReportController::class, 'payment'])->name('report.payment');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('report.revenue')->middleware('can:report-revenue');
        Route::get('/popular-wahana', [ReportController::class, 'popularWahana'])->name('report.popular-wahana')->middleware('can:report-popular-wahana');

        Route::get('/detail-transaction-modal/{id}', [ReportController::class, 'detailTransactionModal'])->name('report.detail-transaction-modal');
    });

    // Printer (Web Bluetooth)
    Route::prefix('printer')->group(function () {
        Route::get('/test', [PrinterTestController::class, 'index'])->name('printer.test');
        Route::get('/receipt-data', [PrinterTestController::class, 'getReceiptData'])->name('printer.receipt-data');
        Route::get('/transaction-data/{id}', [PrinterTestController::class, 'getTransactionData'])->name('printer.transaction-data');
        Route::get('/ticket-data/{id}', [PrinterTestController::class, 'getTicketData'])->name('printer.ticket-data');
        Route::get('/tickets-data/{transactionId}', [PrinterTestController::class, 'getTicketsData'])->name('printer.tickets-data');
    });

    Route::prefix('laravel-filemanager')->group(function () {
        Lfm::routes();
    });
    Route::get('/filemanager-view', [FileManagerController::class, 'index'])->name('filemanager');

    Route::get('/setting', [SettingController::class, 'index'])->name('setting');
    Route::post('/setting', [SettingController::class, 'save'])->name('setting.save');

    Route::get('/audit-trace', [AuditTraceController::class, 'index'])->name('audit-trace');
    Route::get('audit-trace/{id}/detail', [AuditTraceController::class, 'detail'])->name('audit-trace.detail');

    Route::get('change-password', [AuthController::class, 'changePassword'])->name('change-password');
    Route::post('change-password', [AuthController::class, 'saveNewPassword'])->name('save-new-password');
});

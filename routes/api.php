<?php

use App\Http\Controllers\Api\V1\AccountingController;
use App\Http\Controllers\Api\V1\AuctionController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DisbursementController;
use App\Http\Controllers\Api\V1\GoldReleaseController;
use App\Http\Controllers\Api\V1\InterestController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\JewelleryController;
use App\Http\Controllers\Api\V1\KycAadhaarController;
use App\Http\Controllers\Api\V1\KycDocumentController;
use App\Http\Controllers\Api\V1\KycPanController;
use App\Http\Controllers\Api\V1\LoanApprovalController;
use App\Http\Controllers\Api\V1\LoanController;
use App\Http\Controllers\Api\V1\MasterController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PartPaymentController;
use App\Http\Controllers\Api\V1\RenewalController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SettlementController;
use App\Http\Controllers\Api\V1\SyncController;
use App\Http\Controllers\Api\V1\TopupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - v1
|--------------------------------------------------------------------------
| All routes sit behind a single versioned prefix. Public (unauthenticated)
| routes are auth/login/otp only. Everything else requires a valid Sanctum
| token, and role-restricted actions add the `role:` middleware alias.
*/

Route::prefix('v1')->group(function () {

    // ---------------- Module 1: Authentication (public) ----------------
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('otp/send', [AuthController::class, 'sendOtp']);
        Route::post('otp/verify', [AuthController::class, 'verifyOtp']);
    });

    // ---------------- Everything below requires auth ----------------
    Route::middleware(['auth:sanctum', 'device.binding'])->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('refresh-token', [AuthController::class, 'refreshToken']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('device/bind', [AuthController::class, 'bindDeviceEndpoint']);
            Route::post('mpin/set', [AuthController::class, 'setMpin']);
        });

        // ---------------- Module 2: Customer Management ----------------
        Route::prefix('customer')->group(function () {
            Route::post('/', [CustomerController::class, 'store']);
            Route::get('search', [CustomerController::class, 'search']);
            Route::post('duplicate-check', [CustomerController::class, 'duplicateCheck']);
            Route::get('{customer}', [CustomerController::class, 'show']);
            Route::post('{customer}/nominee', [CustomerController::class, 'addNominee']);
            Route::post('{customer}/family-member', [CustomerController::class, 'addFamilyMember']);
            Route::post('{customer}/merge', [CustomerController::class, 'merge'])
                ->middleware('role:REGIONAL_MANAGER,ADMIN');
        });

        // ---------------- Module 3-5: KYC ----------------
        Route::prefix('kyc')->group(function () {
            Route::post('aadhaar/qr-scan', [KycAadhaarController::class, 'qrScan']);
            Route::post('aadhaar/offline-xml', [KycAadhaarController::class, 'offlineXml']);
            Route::post('aadhaar/face-auth', [KycAadhaarController::class, 'faceAuth']);
            Route::get('aadhaar/{customerId}/masked', [KycAadhaarController::class, 'masked']);

            Route::post('pan/validate', [KycPanController::class, 'validatePan']);

            Route::post('document', [KycDocumentController::class, 'store']);
            Route::get('document/{customerId}', [KycDocumentController::class, 'index']);
            Route::put('document/{kycDocument}/verify', [KycDocumentController::class, 'verify']);
        });

        // ---------------- Module 7: Jewellery Evaluation ----------------
        Route::prefix('jewellery')->group(function () {
            Route::post('evaluate', [JewelleryController::class, 'evaluate'])->middleware('role:APPRAISER');
            Route::get('rate/current', [JewelleryController::class, 'currentRate']);
            Route::post('rate/propose', [JewelleryController::class, 'proposeRate'])->middleware('role:APPRAISER,BRANCH_MANAGER');
            Route::post('rate/{goldRate}/approve', [JewelleryController::class, 'approveRate'])
                ->middleware('role:BRANCH_MANAGER,REGIONAL_MANAGER');
            Route::post('{jewelleryItem}/image', [JewelleryController::class, 'uploadImage']);
            Route::get('{jewelleryItem}/barcode', [JewelleryController::class, 'barcode']);
        });

        // ---------------- Module 8-16: Loan Lifecycle ----------------
        Route::prefix('loan')->group(function () {
            Route::post('calculate', [LoanController::class, 'calculate']);
            Route::post('/', [LoanController::class, 'store']);
            Route::get('pending-approval', [LoanApprovalController::class, 'pending']);

            Route::get('{loan}/emi-schedule', [LoanController::class, 'emiSchedule']);

            Route::post('{loan}/submit-for-approval', [LoanApprovalController::class, 'submit']);
            Route::post('{loan}/approve', [LoanApprovalController::class, 'approve'])
                ->middleware('role:APPRAISER,BRANCH_MANAGER,REGIONAL_MANAGER');
            Route::post('{loan}/reject', [LoanApprovalController::class, 'reject'])
                ->middleware('role:APPRAISER,BRANCH_MANAGER,REGIONAL_MANAGER');
            Route::post('{loan}/override', [LoanApprovalController::class, 'override'])
                ->middleware('role:REGIONAL_MANAGER,ADMIN');

            Route::post('{loan}/disburse', [DisbursementController::class, 'disburse'])->middleware('role:CASHIER');

            Route::get('{loan}/renewal-eligibility', [RenewalController::class, 'eligibility']);
            Route::post('{loan}/renew', [RenewalController::class, 'renew']);

            Route::get('{loan}/topup/eligibility', [TopupController::class, 'eligibility']);
            Route::post('{loan}/topup/approve', [TopupController::class, 'approve'])
                ->middleware('role:BRANCH_MANAGER,REGIONAL_MANAGER');
            Route::post('{loan}/topup/disburse', [TopupController::class, 'disburse'])->middleware('role:CASHIER');

            Route::get('{loan}/interest/due', [InterestController::class, 'due']);
            Route::post('{loan}/interest/collect', [InterestController::class, 'collect'])->middleware('role:CASHIER');

            Route::post('{loan}/part-payment', [PartPaymentController::class, 'partPayment'])->middleware('role:CASHIER');
            Route::post('{loan}/reload', [PartPaymentController::class, 'reload']);

            Route::get('{loan}/closure-statement', [SettlementController::class, 'closureStatement']);
            Route::post('{loan}/settle', [SettlementController::class, 'settle'])->middleware('role:CASHIER,BRANCH_MANAGER');

            Route::post('{loan}/gold-release/verify-id', [GoldReleaseController::class, 'verifyId']);
            Route::post('goldrelease/{goldRelease}/capture-signature', [GoldReleaseController::class, 'captureSignature']);
            Route::post('goldrelease/{goldRelease}/complete', [GoldReleaseController::class, 'complete'])
                ->middleware('role:BRANCH_MANAGER');
        });

        // ---------------- Module 17: Auction ----------------
        Route::prefix('auction')->middleware('role:BRANCH_MANAGER,REGIONAL_MANAGER,ADMIN')->group(function () {
            Route::post('schedule', [AuctionController::class, 'schedule']);
            Route::post('{auctionSchedule}/notice', [AuctionController::class, 'notice']);
            Route::post('{auctionSchedule}/bidder', [AuctionController::class, 'addBidder']);
            Route::post('{auctionSchedule}/bid', [AuctionController::class, 'placeBid']);
            Route::post('{auctionSchedule}/winner', [AuctionController::class, 'declareWinner']);
            Route::post('{auctionSchedule}/settle', [AuctionController::class, 'settle']);
        });

        // ---------------- Module 18: Notifications ----------------
        Route::post('notify/{channel}', [NotificationController::class, 'send']);

        // ---------------- Module 20: Dashboard ----------------
        Route::get('dashboard/summary', [DashboardController::class, 'summary']);

        // ---------------- Module 21: Reports ----------------
        Route::get('reports/{reportCode}', [ReportController::class, 'generate']);

        // ---------------- Module 22: Accounting ----------------
        Route::prefix('accounting')->middleware('role:FINANCE,ADMIN')->group(function () {
            Route::post('voucher', [AccountingController::class, 'storeVoucher']);
            Route::get('ledger/{customerId}', [AccountingController::class, 'customerLedger']);
        });

        // ---------------- Module 23: Inventory ----------------
        Route::prefix('inventory')->group(function () {
            Route::post('packet', [InventoryController::class, 'store']);
            Route::post('packet/{goldPacket}/transfer', [InventoryController::class, 'transfer']);
            Route::get('vault/{branchId}/status', [InventoryController::class, 'vaultStatus']);
        });

        // ---------------- Module 25: Offline Sync ----------------
        Route::prefix('sync')->group(function () {
            Route::post('upload-queue', [SyncController::class, 'uploadQueue']);
            Route::get('download-delta', [SyncController::class, 'downloadDelta']);
        });

        // ---------------- Module 26: Admin / Masters ----------------
        Route::prefix('master')->middleware('role:ADMIN,OPERATIONS')->group(function () {
            Route::get('branch', [MasterController::class, 'branches']);
            Route::post('branch', [MasterController::class, 'storeBranch']);
            Route::get('loan-product', [MasterController::class, 'loanProducts']);
            Route::post('loan-product', [MasterController::class, 'storeLoanProduct']);
            Route::get('role', [MasterController::class, 'roles']);
        });
    });
});

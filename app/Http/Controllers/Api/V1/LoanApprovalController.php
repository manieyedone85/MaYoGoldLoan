<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanApprovalLog;
use App\Models\LoanApprovalLimit;
use App\Models\LoanApprovalWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanApprovalController extends Controller
{
    private const STAGE_ORDER = ['APPRAISER', 'MANAGER', 'REGIONAL_MANAGER', 'HO'];

    /**
     * POST /api/v1/loan/{id}/submit-for-approval
     */
    public function submit(Request $request, Loan $loan): JsonResponse
    {
        abort_if($loan->status !== 'DRAFT', 422, 'Only draft loans can be submitted.');

        LoanApprovalWorkflow::create([
            'loan_id' => $loan->id,
            'current_stage' => 'APPRAISER',
            'status' => 'PENDING',
        ]);

        $loan->update(['status' => 'PENDING_APPROVAL']);

        return response()->json(['message' => 'Submitted for approval.']);
    }

    /**
     * POST /api/v1/loan/{id}/approve
     * Maker-Checker: actioning user must never equal loans.created_by.
     */
    public function approve(Request $request, Loan $loan): JsonResponse
    {
        $user = $request->user();
        $workflow = $loan->approvalWorkflow;

        abort_if(! $workflow || $workflow->status !== 'PENDING', 422, 'No pending approval for this loan.');
        abort_if($user->id === $loan->created_by, 403, 'Maker cannot approve their own submission.');

        $limit = LoanApprovalLimit::where('role_id', $user->role_id)->first();
        if ($limit && $loan->sanctioned_amount > $limit->max_amount) {
            $nextStage = self::STAGE_ORDER[array_search($workflow->current_stage, self::STAGE_ORDER) + 1] ?? 'HO';
            $workflow->update(['current_stage' => $nextStage]);

            LoanApprovalLog::create([
                'loan_id' => $loan->id,
                'stage' => $workflow->current_stage,
                'action' => 'ESCALATED',
                'actioned_by' => $user->id,
            ]);

            return response()->json(['message' => "Escalated to {$nextStage} (exceeds approval limit)."]);
        }

        $workflow->update(['status' => 'APPROVED']);
        $loan->update(['status' => 'APPROVED']);

        LoanApprovalLog::create([
            'loan_id' => $loan->id,
            'stage' => $workflow->current_stage,
            'action' => 'APPROVE',
            'actioned_by' => $user->id,
        ]);

        return response()->json(['message' => 'Loan approved.']);
    }

    /**
     * POST /api/v1/loan/{id}/reject
     */
    public function reject(Request $request, Loan $loan): JsonResponse
    {
        $data = $request->validate(['remarks' => ['required', 'string']]);

        if ($loan->approvalWorkflow) {
            $loan->approvalWorkflow->update(['status' => 'REJECTED']);
        }
        $loan->update(['status' => 'REJECTED']);

        LoanApprovalLog::create([
            'loan_id' => $loan->id,
            'stage' => $loan->approvalWorkflow ? $loan->approvalWorkflow->current_stage : 'APPRAISER',
            'action' => 'REJECT',
            'actioned_by' => $request->user()->id,
            'remarks' => $data['remarks'],
        ]);

        return response()->json(['message' => 'Loan rejected.']);
    }

    /**
     * POST /api/v1/loan/{id}/override  (Regional Manager / HO only)
     */
    public function override(Request $request, Loan $loan): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['REGIONAL_MANAGER', 'ADMIN']), 403);

        $data = $request->validate(['remarks' => ['required', 'string']]);

        $loan->update(['status' => 'APPROVED']);
        if ($loan->approvalWorkflow) {
            $loan->approvalWorkflow->update(['status' => 'APPROVED']);
        }

        LoanApprovalLog::create([
            'loan_id' => $loan->id,
            'stage' => 'OVERRIDE',
            'action' => 'OVERRIDE',
            'actioned_by' => $request->user()->id,
            'remarks' => $data['remarks'],
        ]);

        return response()->json(['message' => 'Loan approval overridden.']);
    }

    /**
     * GET /api/v1/loan/pending-approval?role=
     */
    public function pending(Request $request): JsonResponse
    {
        $roleCode = $request->user()->role ? $request->user()->role->code : null;
        $stage = $roleCode === 'BRANCH_MANAGER' ? 'MANAGER' : $request->query('stage', 'APPRAISER');

        $loans = Loan::whereHas('approvalWorkflow', function ($q) use ($stage) {
            $q->where('current_stage', $stage)->where('status', 'PENDING');
        })
            ->with('customer')
            ->get();

        return response()->json(['data' => $loans]);
    }
}

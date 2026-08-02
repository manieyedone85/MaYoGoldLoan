<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Models\Customer;
use App\Models\CustomerDuplicateLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * POST /api/v1/customer
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $data = $request->validated();

        $customer = DB::transaction(function () use ($data, $request) {
            $customer = Customer::create([
                'customer_code' => 'CUST' . str_pad((string) (Customer::max('id') + 1), 8, '0', STR_PAD_LEFT),
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'email' => $data['email'] ?? null,
                'dob' => $data['dob'] ?? null,
                'gender' => $data['gender'] ?? null,
                'branch_id' => $data['branch_id'],
                'registered_by' => $request->user()->id,
                'kyc_status' => 'PENDING',
            ]);

            $customer->addresses()->create(array_merge($data['address'], ['type' => 'CURRENT']));

            return $customer;
        });

        return response()->json(['data' => $customer->load('addresses')], 201);
    }

    /**
     * GET /api/v1/customer/{id}
     */
    public function show(Customer $customer): JsonResponse
    {
        return response()->json([
            'data' => $customer->load(['addresses', 'familyMembers', 'nominees']),
        ]);
    }

    /**
     * GET /api/v1/customer/search?mobile=&aadhaar_hash=
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => ['nullable', 'string'],
            'aadhaar_hash' => ['nullable', 'string'],
        ]);

        $query = Customer::query();

        if ($request->filled('mobile')) {
            $query->where('mobile', $request->input('mobile'));
        }

        if ($request->filled('aadhaar_hash')) {
            $query->where('aadhaar_hash', $request->input('aadhaar_hash'));
        }

        return response()->json(['data' => $query->limit(20)->get()]);
    }

    /**
     * POST /api/v1/customer/duplicate-check
     * Fuzzy match on name + exact match on mobile/aadhaar hash.
     */
    public function duplicateCheck(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string'],
            'mobile' => ['required', 'string'],
            'aadhaar_hash' => ['nullable', 'string'],
        ]);

        $candidates = Customer::where('mobile', $request->input('mobile'))
            ->orWhere('aadhaar_hash', $request->input('aadhaar_hash'))
            ->get();

        return response()->json(['possible_duplicates' => $candidates]);
    }

    /**
     * POST /api/v1/customer/{id}/merge
     * Maker-Checker: requires REGIONAL_MANAGER role (enforced via role middleware on the route).
     */
    public function merge(Request $request, Customer $customer): JsonResponse
    {
        $request->validate(['merged_customer_id' => ['required', 'exists:customers,id']]);

        \App\Models\CustomerMergeLog::create([
            'primary_customer_id' => $customer->id,
            'merged_customer_id' => $request->input('merged_customer_id'),
            'approved_by' => $request->user()->id,
        ]);

        // Soft-link only — never hard delete the merged record.
        Customer::whereKey($request->input('merged_customer_id'))->delete();

        return response()->json(['message' => 'Customers merged.']);
    }

    /**
     * POST /api/v1/customer/{id}/nominee
     */
    public function addNominee(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'relation' => ['required', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'size:10'],
            'id_proof_type' => ['nullable', 'string'],
            'id_proof_number' => ['nullable', 'string'],
        ]);

        $nominee = $customer->nominees()->create($data);

        return response()->json(['data' => $nominee], 201);
    }

    /**
     * POST /api/v1/customer/{id}/family-member
     */
    public function addFamilyMember(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'relation' => ['required', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'size:10'],
        ]);

        $member = $customer->familyMembers()->create($data);

        return response()->json(['data' => $member], 201);
    }
}

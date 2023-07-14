<?php

namespace App\Http\Controllers;

use App\Enums\LoanStatus;
use App\Http\Requests\UpdateRepaymentRequest;
use App\Interfaces\RepaymentRepositoryInterface;
use App\Models\Repayment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RepaymentController extends Controller
{
    private RepaymentRepositoryInterface $repaymentRepository;

    public function __construct(RepaymentRepositoryInterface $repaymentRepository)
    {
        $this->repaymentRepository = $repaymentRepository;
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRepaymentRequest $request, Repayment $repayment)
    {
        $this->authorize('update', $repayment);

        if ($repayment->state != LoanStatus::APPROVED) {
            return response()->json(['message' => 'The loan has not been approved yet'], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();
            $this->repaymentRepository->payForRepayment($repayment);
            DB::commit();

            return response()->json(['message' => 'Repayment successfully paid']);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

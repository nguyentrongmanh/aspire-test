<?php

namespace App\Http\Controllers;

use App\Enums\LoanStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Resources\LoanResource;
use App\Http\Resources\LoanResourceCollection;
use App\Models\Loan;
use App\Models\Repayment;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $loans = new Loan();
        if (auth()->user()->role == UserRole::USER) {
            $loans = Loan::where('user_id', auth()->user()->id);
        }

        return (new LoanResourceCollection($loans->paginate()))->response();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLoanRequest $request)
    {
        try {
            DB::beginTransaction();
            $loan = new Loan();
            $loan->amount = $request->amount;
            $loan->term = $request->term;
            $loan->state = LoanStatus::PENDING;
            $loan->user_id = Auth::user()->id;
            $loan->submitted_date = Carbon::now();
            $loan->save();

            $submittedDate = Carbon::now();

            $repayments = [];
            $repaymentAmount = round($request->amount / $request->term, 2);

            for ($i = 0; $i < $request->term; $i++) {
                $dueDate = Carbon::now()->addWeeks($i + 1);

                $repayments[] = [
                    'loan_id' => $loan->id,
                    'due_date' => $dueDate,
                    'amount' => $repaymentAmount,
                    'state' => LoanStatus::PENDING,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            Repayment::insert($repayments);

            DB::commit();

            return (new LoanResource($loan))->response()->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Loan $loan)
    {
        $this->authorize('view', $loan);

        return (new LoanResource($loan))->response();
    }

    /**
     * Admin approve the specified loan.
     *
     * @param  \App\Http\Requests\UpdateLoanRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function approve(Loan $loan)
    {
        $this->authorize('approve', $loan);
        DB::transaction(function () use ($loan) {
            $loan->state = LoanStatus::APPROVED;
            $loan->approved_date = Carbon::now()->format("Y-m-d");
            $loan->save();

            Repayment::where('loan_id', $loan->id)->update([
                'state' => LoanStatus::APPROVED
            ]);
        });

        return response()->json(['message' => 'Loan approved successfully']);
    }
}

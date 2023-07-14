<?php

namespace App\Http\Controllers;

use App\Enums\LoanStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Resources\LoanResource;
use App\Http\Resources\LoanResourceCollection;
use App\Interfaces\LoanRepositoryInterface;
use App\Models\Loan;
use App\Models\Repayment;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    private LoanRepositoryInterface $loanRepository;

    public function __construct(LoanRepositoryInterface $loanRepository)
    {
        $this->loanRepository = $loanRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $loans = new Loan();
        if (auth()->user()->role == UserRole::USER) {
            $loans = Loan::byUserId(auth()->user()->id);
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

            $loan = $this->loanRepository->createLoan([
                'amount' => $request->amount,
                'term' => $request->term,
            ]);

            $this->loanRepository->createRepaymentsByLoan($loan);

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
        try {
            DB::beginTransaction();

            $loan->state = LoanStatus::APPROVED;
            $loan->approved_date = Carbon::now()->format('Y-m-d');
            $loan->save();

            Repayment::where('loan_id', $loan->id)->update([
                'state' => LoanStatus::APPROVED,
            ]);

            DB::commit();

            return response()->json(['message' => 'Loan approved successfully']);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

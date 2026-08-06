<?php

namespace App\Http\Controllers;

use App\Models\CompanyAddress;
use App\Models\EmployeeDetails;
use App\Models\LanguageSetting;
use App\Models\User;
use App\Scopes\ActiveScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PublicEmployeeController extends Controller
{

    public function show(User $employee)
    {
        abort_unless(request()->hasValidSignature(), 403);

        $employee = User::withoutGlobalScope(ActiveScope::class)
            ->with([
                'employeeDetail.designation',
                'employeeDetail.department',
                'employeeDetail.reportingTo',
                'roles',
                'country',
            ])
            ->findOrFail($employee->id);

        abort_unless($employee->hasRole('employee'), 404);

        $companyAddress = null;

        if ($employee->employeeDetail?->company_address_id) {
            $companyAddress = CompanyAddress::find($employee->employeeDetail->company_address_id);
        }

        if (!$companyAddress) {
            $companyAddress = CompanyAddress::where('is_default', 1)
                ->where('company_id', $this->company->id)
                ->first();
        }

        $employeeLanguage = LanguageSetting::where('language_code', $employee->locale)->first();

        return view('employees.public-show', [
            'pageTitle' => $employee->name,
            'employee' => $employee,
            'company' => $this->company,
            'companyAddress' => $companyAddress,
            'employeeLanguage' => $employeeLanguage,
        ]);
    }

    public function verify(Request $request)
    {
        $employee = null;

        if ($request->filled('eid') && $request->filled('key')) {
            $employee = User::withoutGlobalScope(ActiveScope::class)
            ->with([
                'employeeDetail.designation',
                'employeeDetail.department',
            ])
            ->whereHas('employeeDetail', function ($query) use ($request) {
                $query->where('employee_id', $request->eid)
                    ->where('verification_key', $request->key);
            })
            ->first();
        }

        if (!$employee || !$employee->hasRole('employee')) {
            return view('employees.verify', [
                'pageTitle' => 'Employee Verification',
                'isInvalid' => true,
                'verificationCompanyName' => 'AuromindAI Private Limited',
                'verificationPortalSubtitle' => 'Employee Verification Portal',
            ]);
        }

        $employee = User::withoutGlobalScope(ActiveScope::class)
            ->with([
                'employeeDetail.designation',
                'employeeDetail.department',
            ])
            ->findOrFail($employee->id);

        $employeeDetail = $employee->employeeDetail;
        $companyAddress = null;

        if ($employeeDetail?->company_address_id) {
            $companyAddress = CompanyAddress::find($employeeDetail->company_address_id);
        }

        if (!$companyAddress) {
            $companyAddress = CompanyAddress::where('is_default', 1)
                ->where('company_id', $employee->company_id)
                ->first();
        }

        $status = match ($employee->status) {
            'active' => [
                'label' => 'Verified Employee',
                'message' => 'This employee is currently active and verified by AuromindAI Technologies.',
                'class' => 'is-verified',
                'icon' => 'check-circle',
            ],
            'deactive' => [
                'label' => 'Employee Exited',
                'message' => 'This employee is no longer active in the AuromindAI employee system.',
                'class' => 'is-exited',
                'icon' => 'times-circle',
            ],
            default => [
                'label' => 'Verification Pending',
                'message' => 'This employee record exists, but the current status requires manual verification.',
                'class' => 'is-pending',
                'icon' => 'exclamation-circle',
            ],
        };

        return view('employees.verify', [
            'pageTitle' => 'Employee Verification',
            'employee' => $employee,
            'employeeDetail' => $employeeDetail,
            'company' => $employee->company,
            'companyAddress' => $companyAddress,
            'verificationStatus' => $status,
            'verificationCompanyName' => 'AuromindAI Private Limited',
            'verificationPortalSubtitle' => 'Employee Verification Portal',
        ]);
    }

    public function setupVerificationKeys(Request $request)
    {
        abort_unless(
            filled(env('EMPLOYEE_VERIFY_SETUP_KEY')) &&
            hash_equals((string) env('EMPLOYEE_VERIFY_SETUP_KEY'), (string) $request->key),
            403
        );

        if (!Schema::hasColumn('employee_details', 'verification_key')) {
            Schema::table('employee_details', function ($table) {
                $table->string('verification_key', 32)->nullable()->after('employee_id');
            });

            Schema::table('employee_details', function ($table) {
                $table->unique('verification_key');
            });
        }

        DB::table('employee_details')
            ->select('id')
            ->whereNull('verification_key')
            ->orderBy('id')
            ->chunkById(100, function ($employees) {
                foreach ($employees as $employee) {
                    do {
                        $key = Str::upper(Str::random(8));
                    } while (DB::table('employee_details')->where('verification_key', $key)->exists());

                    DB::table('employee_details')
                        ->where('id', $employee->id)
                        ->update(['verification_key' => $key]);
                }
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Employee verification key setup completed.',
            'totalEmployees' => EmployeeDetails::count(),
            'employeesWithKey' => EmployeeDetails::whereNotNull('verification_key')->count(),
        ]);
    }
}

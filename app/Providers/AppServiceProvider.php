<?php

namespace App\Providers;

use App\Models\Expense;
use App\Models\Loan;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            static $organisation = null;

            if ($organisation === null) {
                $settings = Setting::pluck('value', 'key')->all();
                $organisation = [
                    'orgName' => ($settings['org_name'] ?? null) ?: 'Open SACCO',
                    'orgPhone' => $settings['org_phone'] ?? null,
                    'orgEmail' => $settings['org_email'] ?? null,
                    'orgAddress' => $settings['org_address'] ?? null,
                    'orgTin' => $settings['org_tin'] ?? null,
                    'orgUmraRegNo' => $settings['org_umra_reg_no'] ?? null,
                    'orgLogoUrl' => filled($settings['org_logo'] ?? null) ? route('settings.logo') : null,
                    'docDisclaimer' => $settings['doc_disclaimer'] ?? null,
                ];
            }

            $view->with($organisation);
        });

        View::composer('layouts.app', function ($view) {
            $notifications = [];

            if (Auth::check()) {
                $pendingLoans = Loan::whereIn('status', ['submitted', 'under_review'])->count();
                if ($pendingLoans > 0) {
                    $notifications[] = [
                        'tone' => 'amber',
                        'title' => $pendingLoans === 1
                            ? '1 loan application awaiting review'
                            : $pendingLoans . ' loan applications awaiting review',
                        'detail' => 'Open the loan register to approve or reject.',
                        'url' => route('loans.index'),
                    ];
                }

                $defaultedLoans = Loan::where('status', 'defaulted')->count();
                if ($defaultedLoans > 0) {
                    $notifications[] = [
                        'tone' => 'red',
                        'title' => $defaultedLoans === 1
                            ? '1 defaulted loan requires recovery'
                            : $defaultedLoans . ' defaulted loans require recovery',
                        'detail' => 'Review the affected members and recovery status.',
                        'url' => route('loans.defaulted'),
                    ];
                }

                $pendingExpenses = Expense::where('status', 'pending')->count();
                if ($pendingExpenses > 0) {
                    $notifications[] = [
                        'tone' => 'green',
                        'title' => $pendingExpenses === 1
                            ? '1 expense awaiting approval'
                            : $pendingExpenses . ' expenses awaiting approval',
                        'detail' => 'Approve or update pending expense records.',
                        'url' => route('expenses.index'),
                    ];
                }
            }

            $view->with([
                'headerNotifications' => $notifications,
                'headerNotificationCount' => count($notifications),
            ]);
        });
    }
}

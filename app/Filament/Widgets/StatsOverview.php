<?php

namespace App\Filament\Widgets;

use App\Models\Dispute;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // 1. Active Users (online in the last 15 minutes)
        $activeUsers = User::where('last_seen_at', '>=', now()->subMinutes(15))->count();

        // 2. Daily Trades (Orders created today)
        $dailyTrades = Order::whereDate('created_at', today())->count();

        // 3. Completed Orders (all time)
        $completedOrders = Order::where('status', 'completed')->count();

        // 4. Open Orders (active trades currently in progress)
        $openOrders = Order::whereIn('status', ['pending', 'paid', 'disputed'])->count();

        // 5. Unresolved Disputes
        $activeDisputes = Dispute::whereNull('resolved_at')->count();

        // 6. Total Trading Volume (USDT of completed orders)
        $volume = Order::where('status', 'completed')->sum('amount_usdt');

        // 7. Total Escrow Fees Revenue (USDT)
        $revenue = Escrow::where('status', 'released')->sum('fee_usdt');

        // 8. Total Deposits Completed
        $deposits = Transaction::where('type', 'deposit')
            ->where('status', 'completed')
            ->sum('amount');

        // 9. Total Withdrawals Completed
        $withdrawals = Transaction::where('type', 'withdrawal')
            ->where('status', 'completed')
            ->sum('amount');

        return [
            Stat::make('Active Users (15m)', $activeUsers)
                ->description('Users currently online')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Daily Trades', $dailyTrades)
                ->description('Orders created today')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Trading Volume', number_format($volume, 2) . ' USDT')
                ->description('Accumulated trading volume')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make('Total Revenue', number_format($revenue, 4) . ' USDT')
                ->description('Total platform fees earned')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Active / Open Orders', $openOrders)
                ->description('Trades in progress')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),

            Stat::make('Active Disputes', $activeDisputes)
                ->description('Unresolved trade disputes')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($activeDisputes > 0 ? 'danger' : 'gray'),

            Stat::make('Total Deposits', number_format($deposits, 2) . ' USDT')
                ->description('Completed deposits')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success'),

            Stat::make('Total Withdrawals', number_format($withdrawals, 2) . ' USDT')
                ->description('Completed withdrawals')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('danger'),
        ];
    }
}

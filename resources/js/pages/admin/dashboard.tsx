import AdminLayout from '@/layouts/admin-layout';
import { AnalyticsData } from '@/types/streaming';
import { Users, DollarSign, Film, TrendingUp, Eye } from 'lucide-react';

interface DashboardProps {
    analytics: AnalyticsData;
}

export default function Dashboard({ analytics }: DashboardProps) {
    const stats = [
        {
            name: 'Total Users',
            value: analytics.totalUsers.toLocaleString(),
            change: '+12%',
            changeType: 'positive',
            icon: Users,
        },
        {
            name: 'Active Subscriptions',
            value: analytics.totalSubscriptions.toLocaleString(),
            change: '+8%',
            changeType: 'positive',
            icon: TrendingUp,
        },
        {
            name: 'Total Revenue',
            value: `$${analytics.totalRevenue.toLocaleString()}`,
            change: `+${analytics.revenueGrowth}%`,
            changeType: 'positive',
            icon: DollarSign,
        },
        {
            name: 'Total Content',
            value: analytics.totalContent.toLocaleString(),
            change: '+15%',
            changeType: 'positive',
            icon: Film,
        },
    ];

    return (
        <AdminLayout
            title="Dashboard"
            header={
                <div>
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                        Dashboard
                    </h1>
                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Welcome back! Here's what's happening with your platform today.
                    </p>
                </div>
            }
        >
            {/* Stats Grid */}
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                {stats.map((stat) => {
                    const Icon = stat.icon;
                    return (
                        <div
                            key={stat.name}
                            className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700"
                        >
                            <div className="flex items-center justify-between">
                                <div className="flex items-center">
                                    <div className="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                        <Icon className="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                </div>
                                <span
                                    className={`text-sm font-medium ${
                                        stat.changeType === 'positive'
                                            ? 'text-green-600 dark:text-green-400'
                                            : 'text-red-600 dark:text-red-400'
                                    }`}
                                >
                                    {stat.change}
                                </span>
                            </div>
                            <div className="mt-4">
                                <p className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    {stat.name}
                                </p>
                                <p className="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                                    {stat.value}
                                </p>
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Charts Grid */}
            <div className="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-2">
                {/* Revenue Chart */}
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Revenue Overview
                    </h3>
                    <div className="h-64 flex items-end justify-between space-x-2">
                        {analytics.revenueByMonth.map((item, index) => (
                            <div key={index} className="flex-1 flex flex-col items-center">
                                <div
                                    className="w-full bg-indigo-500 rounded-t"
                                    style={{
                                        height: `${(item.revenue / Math.max(...analytics.revenueByMonth.map(r => r.revenue))) * 100}%`,
                                    }}
                                ></div>
                                <span className="mt-2 text-xs text-gray-600 dark:text-gray-400">
                                    {item.month}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Top Content */}
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Top Content
                    </h3>
                    <div className="space-y-4">
                        {analytics.topContent.map((item, index) => (
                            <div key={index} className="flex items-center justify-between">
                                <div className="flex items-center space-x-3">
                                    <span className="text-2xl font-bold text-gray-400">
                                        #{index + 1}
                                    </span>
                                    <div>
                                        <p className="text-sm font-medium text-gray-900 dark:text-white">
                                            {item.title}
                                        </p>
                                        <p className="text-xs text-gray-500 dark:text-gray-400 flex items-center mt-1">
                                            <Eye className="w-3 h-3 mr-1" />
                                            {item.views.toLocaleString()} views
                                        </p>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Subscriptions by Package */}
            <div className="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Subscriptions by Package
                </h3>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {analytics.subscriptionsByPackage.map((item, index) => (
                        <div
                            key={index}
                            className="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                        >
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400">
                                {item.package}
                            </p>
                            <p className="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                                {item.count.toLocaleString()}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </AdminLayout>
    );
}

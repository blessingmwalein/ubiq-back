import AdminLayout from '@/layouts/admin-layout';
import { AnalyticsData } from '@/types/streaming';
import { 
    Users, 
    DollarSign, 
    TrendingUp, 
    Eye,
    ArrowUp,
    ArrowDown,
    Activity
} from 'lucide-react';

interface AnalyticsPageProps {
    analytics: AnalyticsData & {
        userGrowth: { month: string; users: number }[];
        topCategories: { category: string; views: number }[];
        deviceBreakdown: { device: string; percentage: number }[];
    };
}

export default function AnalyticsPage({ analytics }: AnalyticsPageProps) {
    return (
        <AdminLayout
            title="Analytics"
            header={
                <div>
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                        Analytics & Insights
                    </h1>
                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Detailed performance metrics and business intelligence
                    </p>
                </div>
            }
        >
            {/* Key Metrics */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Total Revenue</p>
                            <p className="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                                ${analytics.totalRevenue.toLocaleString()}
                            </p>
                            <div className="mt-2 flex items-center text-sm text-green-600">
                                <ArrowUp className="w-4 h-4 mr-1" />
                                {analytics.revenueGrowth}% from last month
                            </div>
                        </div>
                        <div className="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                            <DollarSign className="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Active Users</p>
                            <p className="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                                {analytics.activeUsers.toLocaleString()}
                            </p>
                            <div className="mt-2 flex items-center text-sm text-green-600">
                                <ArrowUp className="w-4 h-4 mr-1" />
                                12% from last month
                            </div>
                        </div>
                        <div className="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <Users className="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Content Views</p>
                            <p className="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                                {analytics.contentViews.toLocaleString()}
                            </p>
                            <div className="mt-2 flex items-center text-sm text-green-600">
                                <ArrowUp className="w-4 h-4 mr-1" />
                                8% from last month
                            </div>
                        </div>
                        <div className="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <Eye className="w-6 h-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Subscriptions</p>
                            <p className="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                                {analytics.totalSubscriptions.toLocaleString()}
                            </p>
                            <div className="mt-2 flex items-center text-sm text-red-600">
                                <ArrowDown className="w-4 h-4 mr-1" />
                                3% from last month
                            </div>
                        </div>
                        <div className="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                            <TrendingUp className="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {/* Revenue Chart */}
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Revenue Trend
                    </h3>
                    <div className="h-64 flex items-end justify-between space-x-2">
                        {analytics.revenueByMonth.map((item, index) => {
                            const maxRevenue = Math.max(...analytics.revenueByMonth.map(r => r.revenue));
                            const height = (item.revenue / maxRevenue) * 100;
                            
                            return (
                                <div key={index} className="flex-1 flex flex-col items-center group">
                                    <div className="relative w-full">
                                        <div
                                            className="w-full bg-gradient-to-t from-indigo-500 to-purple-500 rounded-t transition-all hover:from-indigo-600 hover:to-purple-600 cursor-pointer"
                                            style={{ height: `${height * 2}px` }}
                                        >
                                            <div className="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                                ${item.revenue.toLocaleString()}
                                            </div>
                                        </div>
                                    </div>
                                    <span className="mt-2 text-xs text-gray-600 dark:text-gray-400">
                                        {item.month}
                                    </span>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* User Growth Chart */}
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        User Growth
                    </h3>
                    <div className="h-64 flex items-end justify-between space-x-2">
                        {analytics.userGrowth.map((item, index) => {
                            const maxUsers = Math.max(...analytics.userGrowth.map(u => u.users));
                            const height = (item.users / maxUsers) * 100;
                            
                            return (
                                <div key={index} className="flex-1 flex flex-col items-center group">
                                    <div className="relative w-full">
                                        <div
                                            className="w-full bg-gradient-to-t from-blue-500 to-cyan-500 rounded-t transition-all hover:from-blue-600 hover:to-cyan-600 cursor-pointer"
                                            style={{ height: `${height * 2}px` }}
                                        >
                                            <div className="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                                {item.users.toLocaleString()} users
                                            </div>
                                        </div>
                                    </div>
                                    <span className="mt-2 text-xs text-gray-600 dark:text-gray-400">
                                        {item.month}
                                    </span>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Top Content */}
                <div className="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Top Performing Content
                    </h3>
                    <div className="space-y-4">
                        {analytics.topContent.map((item, index) => (
                            <div key={index} className="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div className="flex items-center space-x-4">
                                    <span className="text-2xl font-bold text-gray-400">#{index + 1}</span>
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
                                <div className="flex items-center space-x-2">
                                    <Activity className="w-5 h-5 text-green-500" />
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Device Breakdown */}
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Device Usage
                    </h3>
                    <div className="space-y-4">
                        {analytics.deviceBreakdown.map((item, index) => (
                            <div key={index}>
                                <div className="flex items-center justify-between mb-2">
                                    <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {item.device}
                                    </span>
                                    <span className="text-sm font-semibold text-gray-900 dark:text-white">
                                        {item.percentage}%
                                    </span>
                                </div>
                                <div className="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div
                                        className="bg-gradient-to-r from-indigo-500 to-purple-500 h-2 rounded-full transition-all"
                                        style={{ width: `${item.percentage}%` }}
                                    ></div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
